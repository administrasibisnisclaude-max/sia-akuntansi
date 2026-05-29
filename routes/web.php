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
    });

    // Utang (AP)
    Route::prefix('ap')->name('ap.')->group(function () {
        Route::resource('bills', ApBillController::class);
        Route::post('bills/{bill}/post',    [ApBillController::class, 'post'])->name('bills.post');
        Route::post('bills/{bill}/payment', [ApBillController::class, 'addPayment'])->name('bills.payment');
    });

    // Inventori
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',            [MovementController::class, 'index'])->name('index');
        Route::get('movements',   [MovementController::class, 'movements'])->name('movements');
        Route::post('adjustment', [MovementController::class, 'adjustment'])->name('adjustment');
    });

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('trial-balance',    [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('balance-sheet',    [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('general-ledger',   [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('cash-flow',        [ReportController::class, 'cashFlow'])->name('cash-flow');
    });
});

require __DIR__.'/auth.php';
