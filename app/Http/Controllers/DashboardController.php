<?php

namespace App\Http\Controllers;

use App\Models\{Account, ArInvoice, ApBill, Journal};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAr = ArInvoice::whereNotIn('status', ['cancelled', 'paid'])
            ->get()->sum(fn($i) => $i->remainingAmount());

        $totalAp = ApBill::whereNotIn('status', ['cancelled', 'paid'])
            ->get()->sum(fn($b) => $b->remainingAmount());

        $cashAccount = Account::where('account_code', '1101')->first();
        $cashBalance = 0;
        if ($cashAccount) {
            $bal = $cashAccount->getBalanceForPeriod();
            $cashBalance = $bal['debit'] - $bal['credit'];
        }

        $revenues = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->where('accounts.type', 'Revenue')
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;

        $expenses = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->where('accounts.type', 'Expense')
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;

        $netIncome = $revenues - $expenses;

        $recentJournals = Journal::with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        $totalCarbonYtd = DB::table('environmental_impacts')->whereYear('date', now()->year)->sum('carbon_kg') ?? 0;
        $totalWasteYtd  = DB::table('environmental_impacts')->whereYear('date', now()->year)->sum('waste_kg')  ?? 0;

        return view('dashboard.index', compact(
            'totalAr', 'totalAp', 'cashBalance', 'netIncome', 'recentJournals',
            'totalCarbonYtd', 'totalWasteYtd'
        ));
    }
}
