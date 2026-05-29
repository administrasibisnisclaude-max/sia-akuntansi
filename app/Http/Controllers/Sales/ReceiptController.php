<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesReceipt;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = SalesReceipt::with('customer', 'salesInvoice')->latest('date')->paginate(15);
        return view('sales.receipts.index', compact('receipts'));
    }

    public function show(SalesReceipt $receipt)
    {
        $receipt->load('customer', 'salesInvoice', 'paymentMethod', 'journal');
        return view('sales.receipts.show', compact('receipt'));
    }
}
