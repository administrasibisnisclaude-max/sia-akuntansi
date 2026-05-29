<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\{Quotation, QuotationLine, SalesInvoice, SalesInvoiceLine, Customer, Item};
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with('customer')->latest('date')->paginate(15);
        return view('sales.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        return view('sales.quotations.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'date'                => 'required|date',
            'valid_until'         => 'required|date|after_or_equal:date',
            'notes'               => 'nullable|string',
            'discount_amount'     => 'nullable|numeric|min:0',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->lines as $line) {
                $subtotal += $line['qty'] * $line['unit_price'];
            }

            $discountAmount = min((float) $request->input('discount_amount', 0), $subtotal);
            $taxAmount = (float) $request->input('tax_amount', 0);

            $quotation = Quotation::create([
                'quotation_number' => NumberingService::generate('QT', 'quotations', 'quotation_number'),
                'customer_id'      => $request->customer_id,
                'date'             => $request->date,
                'valid_until'      => $request->valid_until,
                'status'           => 'draft',
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'tax_amount'       => $taxAmount,
                'total'            => ($subtotal - $discountAmount) + $taxAmount,
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                QuotationLine::create([
                    'quotation_id' => $quotation->id,
                    'item_id'      => $line['item_id'] ?? null,
                    'description'  => $line['description'],
                    'qty'          => $line['qty'],
                    'unit_price'   => $line['unit_price'],
                    'subtotal'     => $line['qty'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()->route('sales.quotations.index')->with('success', 'Penawaran harga berhasil dibuat.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('lines.item', 'customer', 'salesInvoices');
        return view('sales.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        abort_if($quotation->status !== 'draft', 403, 'Hanya penawaran draft yang dapat diedit.');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)->orderBy('name')->get();
        $quotation->load('lines');
        return view('sales.quotations.edit', compact('quotation', 'customers', 'items'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        abort_if($quotation->status !== 'draft', 403, 'Hanya penawaran draft yang dapat diedit.');

        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'date'                => 'required|date',
            'valid_until'         => 'required|date|after_or_equal:date',
            'notes'               => 'nullable|string',
            'discount_amount'     => 'nullable|numeric|min:0',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $quotation) {
            $subtotal = 0;
            foreach ($request->lines as $line) {
                $subtotal += $line['qty'] * $line['unit_price'];
            }

            $discountAmount = min((float) $request->input('discount_amount', 0), $subtotal);
            $taxAmount = (float) $request->input('tax_amount', 0);

            $quotation->update([
                'customer_id'     => $request->customer_id,
                'date'            => $request->date,
                'valid_until'     => $request->valid_until,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => ($subtotal - $discountAmount) + $taxAmount,
                'notes'           => $request->notes,
            ]);

            $quotation->lines()->delete();

            foreach ($request->lines as $line) {
                QuotationLine::create([
                    'quotation_id' => $quotation->id,
                    'item_id'      => $line['item_id'] ?? null,
                    'description'  => $line['description'],
                    'qty'          => $line['qty'],
                    'unit_price'   => $line['unit_price'],
                    'subtotal'     => $line['qty'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()->route('sales.quotations.show', $quotation)->with('success', 'Penawaran harga berhasil diperbarui.');
    }

    public function send(Quotation $quotation)
    {
        abort_if($quotation->status !== 'draft', 403, 'Penawaran harus berstatus draft untuk dikirim.');
        $quotation->update(['status' => 'sent']);
        return back()->with('success', 'Penawaran berhasil dikirim ke pelanggan.');
    }

    public function accept(Quotation $quotation)
    {
        abort_if($quotation->status !== 'sent', 403, 'Penawaran harus berstatus terkirim untuk diterima.');
        $quotation->update(['status' => 'accepted']);
        return back()->with('success', 'Penawaran berhasil diterima.');
    }

    public function convertToInvoice(Quotation $quotation)
    {
        abort_if($quotation->status !== 'accepted', 403, 'Penawaran harus diterima sebelum dikonversi.');
        abort_if($quotation->salesInvoices()->exists(), 403, 'Penawaran ini sudah dikonversi menjadi faktur.');

        $quotation->load('lines.item', 'customer');

        $invoice = DB::transaction(function () use ($quotation) {
            $invoice = SalesInvoice::create([
                'invoice_number' => NumberingService::generate('SI', 'sales_invoices', 'invoice_number'),
                'quotation_id'   => $quotation->id,
                'customer_id'    => $quotation->customer_id,
                'date'           => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'status'         => 'draft',
                'subtotal'        => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount'      => $quotation->tax_amount,
                'total'           => $quotation->total,
                'notes'          => $quotation->notes,
                'created_by'     => auth()->id(),
            ]);

            foreach ($quotation->lines as $line) {
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'item_id'          => $line->item_id,
                    'description'      => $line->description,
                    'qty'              => $line->qty,
                    'unit_price'       => $line->unit_price,
                    'subtotal'         => $line->subtotal,
                ]);
            }

            return $invoice;
        });

        return redirect()->route('sales.invoices.show', $invoice)->with('success', 'Penawaran berhasil dikonversi menjadi faktur penjualan.');
    }

    public function printPdf(Quotation $quotation)
    {
        $quotation->load(['customer', 'lines.item']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.quotations.pdf', compact('quotation'));
        return $pdf->stream('penawaran-' . $quotation->quotation_number . '.pdf');
    }

    public function destroy(Quotation $quotation)
    {
        abort_if($quotation->status !== 'draft', 403, 'Hanya penawaran draft yang dapat dihapus.');
        $quotation->delete();
        return redirect()->route('sales.quotations.index')->with('success', 'Penawaran berhasil dihapus.');
    }
}
