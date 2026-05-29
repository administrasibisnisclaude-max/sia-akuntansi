<?php

namespace App\Services;

use App\Models\{Journal, JournalLine, ArInvoice, ArPayment, ApBill, ApPayment, SalesInvoice, SalesReceipt};
use Illuminate\Support\Facades\DB;

class JournalService
{
    public static function validateBalance(array $lines): bool
    {
        $debit  = collect($lines)->sum('debit');
        $credit = collect($lines)->sum('credit');
        return abs($debit - $credit) < 0.01;
    }

    public static function createFromArInvoice(ArInvoice $invoice, int $userId): Journal
    {
        return DB::transaction(function () use ($invoice, $userId) {
            $journal = Journal::create([
                'journal_number'  => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'            => $invoice->date,
                'description'     => "Invoice {$invoice->invoice_number}",
                'status'          => 'posted',
                'reference'       => $invoice->invoice_number,
                'reference_type'  => 'ar_invoice',
                'created_by'      => $userId,
                'posted_by'       => $userId,
                'posted_at'       => now(),
            ]);

            $customer = $invoice->customer;
            $arAccountId = $customer->ar_account_id ?? self::getDefaultAccount('1200');

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $arAccountId,
                'description' => "Piutang - {$customer->name}",
                'debit'       => $invoice->total,
                'credit'      => 0,
                'order'       => 1,
            ]);

            $order = 2;
            foreach ($invoice->lines as $line) {
                $salesAccountId = $line->item?->sales_account_id ?? self::getDefaultAccount('4100');
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $salesAccountId,
                    'description' => $line->description,
                    'debit'       => 0,
                    'credit'      => $line->subtotal,
                    'order'       => $order++,
                ]);

                // COGS + inventory movement for products
                if ($line->item && $line->item->type === 'product' && $line->item->cogs_account_id) {
                    $cogsAmount = $line->qty * $line->item->buy_price;
                    if ($cogsAmount > 0) {
                        JournalLine::create([
                            'journal_id'  => $journal->id,
                            'account_id'  => $line->item->cogs_account_id,
                            'description' => "HPP - {$line->description}",
                            'debit'       => $cogsAmount,
                            'credit'      => 0,
                            'order'       => $order++,
                        ]);
                        if ($line->item->inventory_account_id) {
                            JournalLine::create([
                                'journal_id'  => $journal->id,
                                'account_id'  => $line->item->inventory_account_id,
                                'description' => "Persediaan - {$line->description}",
                                'debit'       => 0,
                                'credit'      => $cogsAmount,
                                'order'       => $order++,
                            ]);
                        }
                    }
                }
            }

            if ($invoice->tax_amount > 0) {
                $taxAccount = self::getDefaultAccount('2200');
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $taxAccount,
                    'description' => 'PPN Keluaran',
                    'debit'       => 0,
                    'credit'      => $invoice->tax_amount,
                    'order'       => $order,
                ]);
            }

            return $journal;
        });
    }

    public static function createFromArPayment(ArPayment $payment, int $userId): Journal
    {
        return DB::transaction(function () use ($payment, $userId) {
            $journal = Journal::create([
                'journal_number'  => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'            => $payment->date,
                'description'     => "Penerimaan {$payment->payment_number}",
                'status'          => 'posted',
                'reference'       => $payment->payment_number,
                'reference_type'  => 'ar_payment',
                'created_by'      => $userId,
                'posted_by'       => $userId,
                'posted_at'       => now(),
            ]);

            $cashAccountId = $payment->paymentMethod->account_id ?? self::getDefaultAccount('1101');
            $arAccountId   = $payment->customer->ar_account_id ?? self::getDefaultAccount('1200');

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $cashAccountId,
                'description' => "Penerimaan dari {$payment->customer->name}",
                'debit'       => $payment->amount,
                'credit'      => 0,
                'order'       => 1,
            ]);

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $arAccountId,
                'description' => "Piutang - {$payment->customer->name}",
                'debit'       => 0,
                'credit'      => $payment->amount,
                'order'       => 2,
            ]);

            return $journal;
        });
    }

    public static function createFromApBill(ApBill $bill, int $userId): Journal
    {
        return DB::transaction(function () use ($bill, $userId) {
            $journal = Journal::create([
                'journal_number'  => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'            => $bill->date,
                'description'     => "Tagihan {$bill->bill_number}",
                'status'          => 'posted',
                'reference'       => $bill->bill_number,
                'reference_type'  => 'ap_bill',
                'created_by'      => $userId,
                'posted_by'       => $userId,
                'posted_at'       => now(),
            ]);

            $vendor = $bill->vendor;
            $apAccountId = $vendor->ap_account_id ?? self::getDefaultAccount('2100');

            $order = 1;
            foreach ($bill->lines as $line) {
                $purchaseAccountId = $line->item?->purchase_account_id ?? self::getDefaultAccount('5100');
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $purchaseAccountId,
                    'description' => $line->description,
                    'debit'       => $line->subtotal,
                    'credit'      => 0,
                    'order'       => $order++,
                ]);
            }

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $apAccountId,
                'description' => "Utang kepada {$vendor->name}",
                'debit'       => 0,
                'credit'      => $bill->total,
                'order'       => $order,
            ]);

            return $journal;
        });
    }

    public static function createFromApPayment(ApPayment $payment, int $userId): Journal
    {
        return DB::transaction(function () use ($payment, $userId) {
            $journal = Journal::create([
                'journal_number'  => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'            => $payment->date,
                'description'     => "Pembayaran {$payment->payment_number}",
                'status'          => 'posted',
                'reference'       => $payment->payment_number,
                'reference_type'  => 'ap_payment',
                'created_by'      => $userId,
                'posted_by'       => $userId,
                'posted_at'       => now(),
            ]);

            $apAccountId   = $payment->vendor->ap_account_id ?? self::getDefaultAccount('2100');
            $cashAccountId = $payment->paymentMethod->account_id ?? self::getDefaultAccount('1101');

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $apAccountId,
                'description' => "Utang kepada {$payment->vendor->name}",
                'debit'       => $payment->amount,
                'credit'      => 0,
                'order'       => 1,
            ]);

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $cashAccountId,
                'description' => "Pembayaran ke {$payment->vendor->name}",
                'debit'       => 0,
                'credit'      => $payment->amount,
                'order'       => 2,
            ]);

            return $journal;
        });
    }

    public static function createFromSalesInvoice(SalesInvoice $invoice, int $userId): Journal
    {
        return DB::transaction(function () use ($invoice, $userId) {
            $journal = Journal::create([
                'journal_number' => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'           => $invoice->date,
                'description'    => "Faktur Penjualan {$invoice->invoice_number}",
                'status'         => 'posted',
                'reference'      => $invoice->invoice_number,
                'reference_type' => 'sales_invoice',
                'created_by'     => $userId,
                'posted_by'      => $userId,
                'posted_at'      => now(),
            ]);

            $customer    = $invoice->customer;
            $arAccountId = $customer->ar_account_id ?? self::getDefaultAccount('1200');

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $arAccountId,
                'description' => "Piutang - {$customer->name}",
                'debit'       => $invoice->total,
                'credit'      => 0,
                'order'       => 1,
            ]);

            $order = 2;
            foreach ($invoice->lines as $line) {
                $salesAccountId = $line->item?->sales_account_id ?? self::getDefaultAccount('4100');
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $salesAccountId,
                    'description' => $line->description,
                    'debit'       => 0,
                    'credit'      => $line->subtotal,
                    'order'       => $order++,
                ]);

                if ($line->item && $line->item->type === 'product' && $line->item->cogs_account_id && $line->item->inventory_account_id) {
                    $cogsAmount = $line->qty * $line->item->buy_price;
                    if ($cogsAmount > 0) {
                        JournalLine::create([
                            'journal_id'  => $journal->id,
                            'account_id'  => $line->item->cogs_account_id,
                            'description' => "HPP - {$line->description}",
                            'debit'       => $cogsAmount,
                            'credit'      => 0,
                            'order'       => $order++,
                        ]);
                        JournalLine::create([
                            'journal_id'  => $journal->id,
                            'account_id'  => $line->item->inventory_account_id,
                            'description' => "Persediaan - {$line->description}",
                            'debit'       => 0,
                            'credit'      => $cogsAmount,
                            'order'       => $order++,
                        ]);
                    }
                }
            }

            if ($invoice->tax_amount > 0) {
                $taxAccount = self::getDefaultAccount('2200');
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $taxAccount,
                    'description' => 'PPN Keluaran',
                    'debit'       => 0,
                    'credit'      => $invoice->tax_amount,
                    'order'       => $order,
                ]);
            }

            return $journal;
        });
    }

    public static function createFromSalesReceipt(SalesReceipt $receipt, int $userId): Journal
    {
        return DB::transaction(function () use ($receipt, $userId) {
            $journal = Journal::create([
                'journal_number' => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'           => $receipt->date,
                'description'    => "Penerimaan {$receipt->receipt_number}",
                'status'         => 'posted',
                'reference'      => $receipt->receipt_number,
                'reference_type' => 'sales_receipt',
                'created_by'     => $userId,
                'posted_by'      => $userId,
                'posted_at'      => now(),
            ]);

            $cashAccountId = $receipt->paymentMethod->account_id ?? self::getDefaultAccount('1101');
            $arAccountId   = $receipt->customer->ar_account_id ?? self::getDefaultAccount('1200');

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $cashAccountId,
                'description' => "Penerimaan dari {$receipt->customer->name}",
                'debit'       => $receipt->amount,
                'credit'      => 0,
                'order'       => 1,
            ]);

            JournalLine::create([
                'journal_id'  => $journal->id,
                'account_id'  => $arAccountId,
                'description' => "Piutang - {$receipt->customer->name}",
                'debit'       => 0,
                'credit'      => $receipt->amount,
                'order'       => 2,
            ]);

            return $journal;
        });
    }

    private static function getDefaultAccount(string $code): int
    {
        return \App\Models\Account::where('account_code', $code)->value('id') ?? 1;
    }
}
