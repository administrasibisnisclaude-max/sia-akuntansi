<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesInvoice, SalesInvoiceLine, SalesReceipt, Customer, Item, PaymentMethod, Quotation};
use App\Services\{JournalService, InventoryService, NumberingService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SalesInvoice::with('customer')->latest('date')->paginate(15);
        foreach ($invoices as $inv) {
            $inv->paid      = $inv->paidAmount();
            $inv->remaining = $inv->remainingAmount();
        }
        return view('sales.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        $quotation = null;
        if ($request->quotation_id) {
            $quotation = Quotation::with('lines.item')->findOrFail($request->quotation_id);
        }
        return view('sales.invoices.create', compact('customers', 'items', 'quotation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'date'                => 'required|date',
            'due_date'            => 'required|date|after_or_equal:date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal  = 0;
            foreach ($request->lines as $line) {
                $subtotal += $line['qty'] * $line['unit_price'];
            }
            $taxAmount = (float) $request->input('tax_amount', 0);

            $invoice = SalesInvoice::create([
                'invoice_number' => NumberingService::generate('SI', 'sales_invoices', 'invoice_number'),
                'quotation_id'   => $request->quotation_id ?: null,
                'customer_id'    => $request->customer_id,
                'date'           => $request->date,
                'due_date'       => $request->due_date,
                'status'         => 'draft',
                'subtotal'       => $subtotal,
                'tax_amount'     => $taxAmount,
                'total'          => $subtotal + $taxAmount,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id'          => $line['item_id'] ?? null,
                    'description'      => $line['description'],
                    'qty'              => $line['qty'],
                    'unit_price'       => $line['unit_price'],
                    'subtotal'         => $line['qty'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()->route('sales.invoices.index')->with('success', 'Faktur penjualan berhasil dibuat.');
    }

    public function show(SalesInvoice $invoice)
    {
        $invoice->load('customer', 'lines.item', 'receipts.paymentMethod', 'journal', 'quotation');
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('sales.invoices.show', compact('invoice', 'paymentMethods'));
    }

    public function edit(SalesInvoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Faktur yang sudah diproses tidak dapat diedit.');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        $invoice->load('lines');
        return view('sales.invoices.edit', compact('invoice', 'customers', 'items'));
    }

    public function update(Request $request, SalesInvoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Faktur yang sudah diproses tidak dapat diedit.');

        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'date'                => 'required|date',
            'due_date'            => 'required|date|after_or_equal:date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $subtotal  = 0;
            foreach ($request->lines as $line) {
                $subtotal += $line['qty'] * $line['unit_price'];
            }
            $taxAmount = (float) $request->input('tax_amount', 0);

            $invoice->update([
                'customer_id' => $request->customer_id,
                'date'        => $request->date,
                'due_date'    => $request->due_date,
                'subtotal'    => $subtotal,
                'tax_amount'  => $taxAmount,
                'total'       => $subtotal + $taxAmount,
                'notes'       => $request->notes,
            ]);

            $invoice->lines()->delete();

            foreach ($request->lines as $line) {
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id'          => $line['item_id'] ?? null,
                    'description'      => $line['description'],
                    'qty'              => $line['qty'],
                    'unit_price'       => $line['unit_price'],
                    'subtotal'         => $line['qty'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()->route('sales.invoices.show', $invoice)->with('success', 'Faktur penjualan berhasil diperbarui.');
    }

    public function post(SalesInvoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Hanya faktur draft yang dapat diposting.');

        $invoice->load('lines.item', 'customer');

        DB::transaction(function () use ($invoice) {
            $journal = JournalService::createFromSalesInvoice($invoice, auth()->id());

            foreach ($invoice->lines as $line) {
                if ($line->item && $line->item->type === 'product') {
                    InventoryService::recordOut($line->item, $line->qty, 'sales_invoice_line', $line->id, auth()->id());
                }
            }

            \App\Services\EnvironmentalService::recordFromLines(
                $invoice->lines,
                'sales_invoice_line',
                $invoice->date->toDateString(),
                auth()->id()
            );

            $invoice->update(['status' => 'posted', 'journal_id' => $journal->id]);
        });

        return back()->with('success', 'Faktur penjualan berhasil diposting.');
    }

    public function addPayment(Request $request, SalesInvoice $invoice)
    {
        abort_if(!in_array($invoice->status, ['posted', 'partial']), 403, 'Pembayaran hanya dapat dilakukan pada faktur yang sudah diposting.');

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'date'              => 'required|date',
            'amount'            => 'required|numeric|min:0.01|max:' . $invoice->remainingAmount(),
            'reference_number'  => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $receipt = SalesReceipt::create([
                'receipt_number'    => NumberingService::generate('RCV', 'sales_receipts', 'receipt_number'),
                'sales_invoice_id'  => $invoice->id,
                'customer_id'       => $invoice->customer_id,
                'payment_method_id' => $request->payment_method_id,
                'date'              => $request->date,
                'amount'            => $request->amount,
                'reference_number'  => $request->reference_number,
                'notes'             => $request->notes,
                'created_by'        => auth()->id(),
            ]);

            $receipt->load('customer', 'paymentMethod');
            $journal = JournalService::createFromSalesReceipt($receipt, auth()->id());
            $receipt->update(['journal_id' => $journal->id]);

            $remaining = $invoice->remainingAmount();
            if ($remaining <= 0.01) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
            }
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(SalesInvoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Hanya faktur draft yang dapat dihapus.');
        $invoice->delete();
        return redirect()->route('sales.invoices.index')->with('success', 'Faktur penjualan berhasil dihapus.');
    }
}
