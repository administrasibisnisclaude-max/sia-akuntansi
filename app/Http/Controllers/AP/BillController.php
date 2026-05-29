<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\{ApBill, ApBillLine, ApPayment, Vendor, Item, PaymentMethod};
use App\Services\{JournalService, InventoryService, NumberingService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index()
    {
        $bills = ApBill::with('vendor')->latest('date')->paginate(20);
        foreach ($bills as $bill) {
            $bill->paid = $bill->paidAmount();
            $bill->remaining = $bill->remainingAmount();
        }
        return view('ap.bills.index', compact('bills'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)->orderBy('name')->get();
        return view('ap.bills.create', compact('vendors', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'            => 'required|exists:vendors,id',
            'date'                 => 'required|date',
            'due_date'             => 'required|date|after_or_equal:date',
            'notes'                => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.description'  => 'required|string',
            'lines.*.qty'          => 'required|numeric|min:0.01',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                $subtotal    += $lineSubtotal;
                $taxAmount   += $lineSubtotal * ($line['tax_rate'] / 100);
            }

            $bill = ApBill::create([
                'bill_number' => NumberingService::generate('BILL', 'ap_bills', 'bill_number'),
                'vendor_id'   => $request->vendor_id,
                'date'        => $request->date,
                'due_date'    => $request->due_date,
                'status'      => 'draft',
                'subtotal'    => $subtotal,
                'tax_amount'  => $taxAmount,
                'total'       => $subtotal + $taxAmount,
                'notes'       => $request->notes,
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                ApBillLine::create([
                    'ap_bill_id'  => $bill->id,
                    'item_id'     => $line['item_id'] ?? null,
                    'description' => $line['description'],
                    'qty'         => $line['qty'],
                    'unit_price'  => $line['unit_price'],
                    'tax_rate'    => $line['tax_rate'],
                    'subtotal'    => $lineSubtotal,
                ]);
            }
        });

        return redirect()->route('ap.bills.index')->with('success', 'Tagihan berhasil dibuat.');
    }

    public function show(ApBill $bill)
    {
        $bill->load('vendor', 'lines.item', 'payments.paymentMethod');
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('ap.bills.show', compact('bill', 'paymentMethods'));
    }

    public function edit(ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->withErrors(['status' => 'Tagihan yang sudah diproses tidak dapat diedit.']);
        }
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)->orderBy('name')->get();
        $bill->load('lines');
        return view('ap.bills.edit', compact('bill', 'vendors', 'items'));
    }

    public function update(Request $request, ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->withErrors(['status' => 'Tagihan yang sudah diproses tidak dapat diedit.']);
        }

        $request->validate([
            'vendor_id'            => 'required|exists:vendors,id',
            'date'                 => 'required|date',
            'due_date'             => 'required|date|after_or_equal:date',
            'notes'                => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.description'  => 'required|string',
            'lines.*.qty'          => 'required|numeric|min:0.01',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $bill) {
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                $subtotal    += $lineSubtotal;
                $taxAmount   += $lineSubtotal * ($line['tax_rate'] / 100);
            }

            $bill->update([
                'vendor_id'  => $request->vendor_id,
                'date'       => $request->date,
                'due_date'   => $request->due_date,
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmount,
                'total'      => $subtotal + $taxAmount,
                'notes'      => $request->notes,
            ]);

            $bill->lines()->delete();

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                ApBillLine::create([
                    'ap_bill_id'  => $bill->id,
                    'item_id'     => $line['item_id'] ?? null,
                    'description' => $line['description'],
                    'qty'         => $line['qty'],
                    'unit_price'  => $line['unit_price'],
                    'tax_rate'    => $line['tax_rate'],
                    'subtotal'    => $lineSubtotal,
                ]);
            }
        });

        return redirect()->route('ap.bills.show', $bill)->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function post(ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->withErrors(['status' => 'Tagihan tidak dapat diposting.']);
        }

        $bill->load('lines.item', 'vendor');

        DB::transaction(function () use ($bill) {
            $journal = JournalService::createFromApBill($bill, auth()->id());

            foreach ($bill->lines as $line) {
                if ($line->item && $line->item->type === 'product') {
                    InventoryService::recordIn($line->item, $line->qty, $line->unit_price, 'ap_bill_line', $line->id, auth()->id());
                }
            }

            $bill->update(['status' => 'received', 'journal_id' => $journal->id]);
        });

        return back()->with('success', 'Tagihan berhasil diposting.');
    }

    public function addPayment(Request $request, ApBill $bill)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'date'              => 'required|date',
            'amount'            => 'required|numeric|min:0.01|max:' . $bill->remainingAmount(),
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $bill) {
            $payment = ApPayment::create([
                'payment_number'    => NumberingService::generate('APPAY', 'ap_payments', 'payment_number'),
                'ap_bill_id'        => $bill->id,
                'vendor_id'         => $bill->vendor_id,
                'payment_method_id' => $request->payment_method_id,
                'date'              => $request->date,
                'amount'            => $request->amount,
                'notes'             => $request->notes,
                'created_by'        => auth()->id(),
            ]);

            $payment->load('vendor', 'paymentMethod');
            $journal = JournalService::createFromApPayment($payment, auth()->id());
            $payment->update(['journal_id' => $journal->id]);

            $remaining = $bill->remainingAmount();
            if ($remaining <= 0.01) {
                $bill->update(['status' => 'paid']);
            } elseif ($bill->paidAmount() > 0) {
                $bill->update(['status' => 'partial']);
            }
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya tagihan draft yang dapat dihapus.']);
        }
        $bill->delete();
        return redirect()->route('ap.bills.index')->with('success', 'Tagihan berhasil dihapus.');
    }
}
