<?php

namespace App\Http\Controllers;

use App\Models\{Account, JournalLine};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function dateRange(Request $request): array
    {
        return [
            $request->input('date_from', now()->startOfYear()->toDateString()),
            $request->input('date_to',   now()->toDateString()),
        ];
    }

    public function trialBalance(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->whereBetween('journals.date', [$from, $to])
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->selectRaw('accounts.account_code, accounts.name, accounts.type, accounts.normal_balance, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->orderBy('accounts.account_code')
            ->get();

        return view('reports.trial-balance', compact('rows', 'from', 'to'));
    }

    public function balanceSheet(Request $request)
    {
        $to = $request->input('date_to', now()->toDateString());

        $accounts = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->where('journals.date', '<=', $to)
            ->whereIn('accounts.type', ['Asset', 'Liability', 'Equity'])
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->selectRaw('accounts.account_code, accounts.name, accounts.type, accounts.normal_balance, SUM(journal_lines.debit) - SUM(journal_lines.credit) as net')
            ->orderBy('accounts.account_code')
            ->get();

        $assets      = $accounts->where('type', 'Asset');
        $liabilities = $accounts->where('type', 'Liability');
        $equity      = $accounts->where('type', 'Equity');

        $netIncome = $this->calculateNetIncome($to);

        return view('reports.balance-sheet', compact('assets', 'liabilities', 'equity', 'netIncome', 'to'));
    }

    public function incomeStatement(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $accounts = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->whereBetween('journals.date', [$from, $to])
            ->whereIn('accounts.type', ['Revenue', 'Expense'])
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->selectRaw('accounts.account_code, accounts.name, accounts.type, accounts.normal_balance, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->orderBy('accounts.account_code')
            ->get();

        $revenues = $accounts->where('type', 'Revenue');
        $expenses = $accounts->where('type', 'Expense');

        $totalRevenue = $revenues->sum(fn($a) => $a->total_credit - $a->total_debit);
        $totalExpense = $expenses->sum(fn($a) => $a->total_debit - $a->total_credit);
        $netIncome    = $totalRevenue - $totalExpense;

        return view('reports.income-statement', compact('revenues', 'expenses', 'totalRevenue', 'totalExpense', 'netIncome', 'from', 'to'));
    }

    public function generalLedger(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $accountId = $request->input('account_id');

        $accounts = Account::orderBy('account_code')->get();
        $lines = collect();
        $account = null;

        if ($accountId) {
            $account = Account::find($accountId);
            $lines = DB::table('journal_lines')
                ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
                ->where('journals.status', 'posted')
                ->where('journal_lines.account_id', $accountId)
                ->whereBetween('journals.date', [$from, $to])
                ->selectRaw('journals.date, journals.journal_number, journals.description, journal_lines.description as line_desc, journal_lines.debit, journal_lines.credit')
                ->orderBy('journals.date')
                ->orderBy('journals.journal_number')
                ->get();
        }

        return view('reports.general-ledger', compact('lines', 'account', 'accounts', 'from', 'to'));
    }

    public function cashFlow(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $cashAccounts = Account::where('account_code', 'like', '110%')->pluck('id');

        $inflows = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journals.status', 'posted')
            ->whereBetween('journals.date', [$from, $to])
            ->whereIn('journal_lines.account_id', $cashAccounts)
            ->selectRaw('SUM(journal_lines.debit) as total_in, SUM(journal_lines.credit) as total_out')
            ->first();

        $totalIn  = $inflows->total_in  ?? 0;
        $totalOut = $inflows->total_out ?? 0;
        $netCash  = $totalIn - $totalOut;

        return view('reports.cash-flow', compact('totalIn', 'totalOut', 'netCash', 'from', 'to'));
    }

    private function calculateNetIncome(string $to): float
    {
        $revenues = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->where('journals.date', '<=', $to)
            ->where('accounts.type', 'Revenue')
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;

        $expenses = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journals.status', 'posted')
            ->where('journals.date', '<=', $to)
            ->where('accounts.type', 'Expense')
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;

        return (float) ($revenues - $expenses);
    }
}
