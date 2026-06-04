<?php

use App\Http\Controllers\{
    DashboardController,
    AccountController,
    CustomerController,
    VendorController,
    ItemController,
    PaymentMethodController,
    JournalController,
    ReportController,
};
use App\Http\Controllers\AR\InvoiceController as ArInvoiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\ReceiptController;
use App\Http\Controllers\AP\BillController as ApBillController;
use App\Http\Controllers\Inventory\MovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('vendors', VendorController::class)->except(['show']);
    Route::resource('items', ItemController::class)->except(['show']);
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);

    // Jurnal Umum
    Route::resource('journals', JournalController::class);
    Route::post('journals/{journal}/post',   [JournalController::class, 'post'])->name('journals.post');
    Route::post('journals/{journal}/unpost', [JournalController::class, 'unpost'])->name('journals.unpost');

    // Piutang (AR)
    Route::prefix('ar')->name('ar.')->group(function () {
        Route::resource('invoices', ArInvoiceController::class);
        Route::post('invoices/{invoice}/post',    [ArInvoiceController::class, 'post'])->name('invoices.post');
        Route::post('invoices/{invoice}/payment', [ArInvoiceController::class, 'addPayment'])->name('invoices.payment');
        Route::get('invoices/{invoice}/pdf',      [ArInvoiceController::class, 'printPdf'])->name('invoices.pdf');
    });

    // Utang (AP)
    Route::prefix('ap')->name('ap.')->group(function () {
        Route::resource('bills', ApBillController::class);
        Route::post('bills/{bill}/post',    [ApBillController::class, 'post'])->name('bills.post');
        Route::post('bills/{bill}/payment', [ApBillController::class, 'addPayment'])->name('bills.payment');
    });

    // Penjualan (Sales)
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::resource('quotations', QuotationController::class);
        Route::post('quotations/{quotation}/send',    [QuotationController::class, 'send'])->name('quotations.send');
        Route::post('quotations/{quotation}/accept',  [QuotationController::class, 'accept'])->name('quotations.accept');
        Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');

        Route::resource('invoices', SalesInvoiceController::class);
        Route::post('invoices/{invoice}/post',    [SalesInvoiceController::class, 'post'])->name('invoices.post');
        Route::post('invoices/{invoice}/payment', [SalesInvoiceController::class, 'addPayment'])->name('invoices.payment');
        Route::get('invoices/{invoice}/pdf',      [SalesInvoiceController::class, 'printPdf'])->name('invoices.pdf');
        Route::get('quotations/{quotation}/pdf',  [QuotationController::class, 'printPdf'])->name('quotations.pdf');

        Route::get('receipts',           [ReceiptController::class, 'index'])->name('receipts.index');
        Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    });

    // Inventori
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',            [MovementController::class, 'index'])->name('index');
        Route::get('movements',   [MovementController::class, 'movements'])->name('movements');
        Route::post('adjustment', [MovementController::class, 'adjustment'])->name('adjustment');
    });

    // Pengaturan
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('trial-balance',    [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('balance-sheet',    [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('general-ledger',   [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('cash-flow',        [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('environmental',    [ReportController::class, 'environmental'])->name('environmental');
        Route::get('sold-products',          [ReportController::class, 'soldProducts'])->name('sold-products');
        Route::get('stock-opname',           [ReportController::class, 'stockOpname'])->name('stock-opname');
        Route::get('pajak',                  [ReportController::class, 'pajak'])->name('pajak');
        Route::get('payment-methods-report', [ReportController::class, 'paymentMethodsReport'])->name('payment-methods-report');
        Route::get('customer-spent',         [ReportController::class, 'customerSpent'])->name('customer-spent');
    });
});

require __DIR__.'/auth.php';
