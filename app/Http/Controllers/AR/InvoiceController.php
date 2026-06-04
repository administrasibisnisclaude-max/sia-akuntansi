<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\{ArInvoice, ArInvoiceLine, ArPayment, Customer, Item, PaymentMethod};
use App\Services\{JournalService, InventoryService, NumberingService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = ArInvoice::with('customer')->latest('date')->paginate(20);
        foreach ($invoices as $inv) {
            $inv->paid = $inv->paidAmount();
            $inv->remaining = $inv->remainingAmount();
        }
        return view('ar.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        return view('ar.invoices.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'date'                 => 'required|date',
            'due_date'             => 'required|date|after_or_equal:date',
            'notes'                => 'nullable|string',
            'discount_amount'      => 'nullable|numeric|min:0',
            'lines'                => 'required|array|min:1',
            'lines.*.description'  => 'required|string',
            'lines.*.qty'          => 'required|numeric|min:0.01',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal   = 0;
            $taxAmount  = 0;

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                $subtotal    += $lineSubtotal;
                $taxAmount   += $lineSubtotal * ($line['tax_rate'] / 100);
            }

            $discountAmount = min((float) $request->input('discount_amount', 0), $subtotal);

            $invoice = ArInvoice::create([
                'invoice_number'  => NumberingService::generate('INV', 'ar_invoices', 'invoice_number'),
                'customer_id'     => $request->customer_id,
                'date'            => $request->date,
                'due_date'        => $request->due_date,
                'status'          => 'draft',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $subtotal - $discountAmount + $taxAmount,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                ArInvoiceLine::create([
                    'ar_invoice_id' => $invoice->id,
                    'item_id'       => $line['item_id'] ?? null,
                    'description'   => $line['description'],
                    'qty'           => $line['qty'],
                    'unit_price'    => $line['unit_price'],
                    'tax_rate'      => $line['tax_rate'],
                    'subtotal'      => $lineSubtotal,
                ]);
            }
        });

        return redirect()->route('ar.invoices.index')->with('success', 'Faktur berhasil dibuat.');
    }

    public function show(ArInvoice $invoice)
    {
        $invoice->load('customer', 'lines.item', 'payments.paymentMethod');
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('ar.invoices.show', compact('invoice', 'paymentMethods'));
    }

    public function edit(ArInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['status' => 'Faktur yang sudah diproses tidak dapat diedit.']);
        }
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        $invoice->load('lines');
        return view('ar.invoices.edit', compact('invoice', 'customers', 'items'));
    }

    public function update(Request $request, ArInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['status' => 'Faktur yang sudah diproses tidak dapat diedit.']);
        }

        $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'date'                 => 'required|date',
            'due_date'             => 'required|date|after_or_equal:date',
            'notes'                => 'nullable|string',
            'discount_amount'      => 'nullable|numeric|min:0',
            'lines'                => 'required|array|min:1',
            'lines.*.description'  => 'required|string',
            'lines.*.qty'          => 'required|numeric|min:0.01',
            'lines.*.unit_price'   => 'required|numeric|min:0',
            'lines.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                $subtotal    += $lineSubtotal;
                $taxAmount   += $lineSubtotal * ($line['tax_rate'] / 100);
            }

            $discountAmount = min((float) $request->input('discount_amount', 0), $subtotal);

            $invoice->update([
                'customer_id'     => $request->customer_id,
                'date'            => $request->date,
                'due_date'        => $request->due_date,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $subtotal - $discountAmount + $taxAmount,
                'notes'           => $request->notes,
            ]);

            $invoice->lines()->delete();

            foreach ($request->lines as $line) {
                $lineSubtotal = $line['qty'] * $line['unit_price'];
                ArInvoiceLine::create([
                    'ar_invoice_id' => $invoice->id,
                    'item_id'       => $line['item_id'] ?? null,
                    'description'   => $line['description'],
                    'qty'           => $line['qty'],
                    'unit_price'    => $line['unit_price'],
                    'tax_rate'      => $line['tax_rate'],
                    'subtotal'      => $lineSubtotal,
                ]);
            }
        });

        return redirect()->route('ar.invoices.show', $invoice)->with('success', 'Faktur berhasil diperbarui.');
    }

    public function post(ArInvoice $invoice)
    {
        if (!in_array($invoice->status, ['draft', 'sent'])) {
            return back()->withErrors(['status' => 'Faktur tidak dapat diposting.']);
        }

        $invoice->load('lines.item', 'customer');

        DB::transaction(function () use ($invoice) {
            $journal = JournalService::createFromArInvoice($invoice, auth()->id());

            foreach ($invoice->lines as $line) {
                if ($line->item && $line->item->type === 'product') {
                    InventoryService::recordOut($line->item, $line->qty, 'ar_invoice_line', $line->id, auth()->id());
                }
            }

            \App\Services\EnvironmentalService::recordFromLines(
                $invoice->lines,
                'ar_invoice_line',
                $invoice->date->toDateString(),
                auth()->id()
            );

            $invoice->update(['status' => 'sent', 'journal_id' => $journal->id]);
        });

        return back()->with('success', 'Faktur berhasil diposting.');
    }

    public function addPayment(Request $request, ArInvoice $invoice)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'date'              => 'required|date',
            'amount'            => 'required|numeric|min:0.01|max:' . round($invoice->remainingAmount(), 2),
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $payment = ArPayment::create([
                'payment_number'    => NumberingService::generate('ARPAY', 'ar_payments', 'payment_number'),
                'ar_invoice_id'     => $invoice->id,
                'customer_id'       => $invoice->customer_id,
                'payment_method_id' => $request->payment_method_id,
                'date'              => $request->date,
                'amount'            => $request->amount,
                'notes'             => $request->notes,
                'created_by'        => auth()->id(),
            ]);

            $payment->load('customer', 'paymentMethod');
            $journal = JournalService::createFromArPayment($payment, auth()->id());
            $payment->update(['journal_id' => $journal->id]);

            $invoice->refresh();
            $remaining = round($invoice->remainingAmount(), 2);
            if ($remaining <= 0) {
                $invoice->update(['status' => 'paid']);
            } elseif ($invoice->paidAmount() > 0) {
                $invoice->update(['status' => 'partial']);
            }
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function printPdf(ArInvoice $invoice)
    {
        $invoice->load(['customer', 'lines.item']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ar.invoices.pdf', compact('invoice'));
        return $pdf->stream('faktur-' . $invoice->invoice_number . '.pdf');
    }

    public function destroy(ArInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya faktur draft yang dapat dihapus.']);
        }
        $invoice->delete();
        return redirect()->route('ar.invoices.index')->with('success', 'Faktur berhasil dihapus.');
    }
}
