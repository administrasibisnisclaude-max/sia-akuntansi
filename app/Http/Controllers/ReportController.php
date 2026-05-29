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

    public function environmental(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->input('date_to',   now()->toDateString());

        $rows = DB::table('environmental_impacts')
            ->join('items', 'items.id', '=', 'environmental_impacts.item_id')
            ->whereBetween('environmental_impacts.date', [$from, $to])
            ->when($request->waste_category, fn($q, $v) => $q->where('environmental_impacts.waste_category', $v))
            ->when($request->carbon_category, fn($q, $v) => $q->where('environmental_impacts.carbon_category', $v))
            ->when($request->source_type, fn($q, $v) => $q->where('environmental_impacts.reference_type', $v))
            ->groupBy('items.id', 'items.name', 'items.item_code',
                      'environmental_impacts.waste_category',
                      'environmental_impacts.carbon_category')
            ->selectRaw('items.id as item_id, items.name as item_name,
                         COALESCE(items.item_code, \'\') as item_code,
                         environmental_impacts.waste_category,
                         environmental_impacts.carbon_category,
                         SUM(environmental_impacts.qty) as total_qty,
                         SUM(environmental_impacts.waste_kg) as total_waste,
                         SUM(environmental_impacts.carbon_kg) as total_carbon')
            ->orderByDesc('total_waste')
            ->get();

        $totalWaste  = $rows->sum('total_waste');
        $totalCarbon = $rows->sum('total_carbon');
        $totalTrx    = \App\Models\EnvironmentalImpact::whereBetween('date', [$from, $to])->count();
        $topWaste    = $rows->sortByDesc('total_waste')->take(5);

        return view('reports.environmental', compact('rows', 'totalWaste', 'totalCarbon', 'totalTrx', 'topWaste', 'from', 'to'));
    }

    public function soldProducts(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->input('date_to',   now()->toDateString());

        $fromSales = DB::table('sales_invoice_lines as sil')
            ->join('items', 'items.id', '=', 'sil.item_id')
            ->join('sales_invoices as si', 'si.id', '=', 'sil.sales_invoice_id')
            ->whereIn('si.status', ['posted','partial','paid'])
            ->whereBetween('si.date', [$from, $to])
            ->whereNotNull('sil.item_id')
            ->when($request->item_id, fn($q,$v)=>$q->where('sil.item_id',$v))
            ->when($request->customer_id, fn($q,$v)=>$q->where('si.customer_id',$v))
            ->groupBy('items.id','items.item_code','items.name','items.unit')
            ->selectRaw('items.id as item_id, items.item_code, items.name as item_name, items.unit,
                         SUM(sil.qty) as total_qty, SUM(sil.subtotal) as total_revenue')
            ->get();

        $fromAr = DB::table('ar_invoice_lines as ail')
            ->join('items', 'items.id', '=', 'ail.item_id')
            ->join('ar_invoices as ai', 'ai.id', '=', 'ail.ar_invoice_id')
            ->whereIn('ai.status', ['sent','partial','paid'])
            ->whereBetween('ai.date', [$from, $to])
            ->whereNotNull('ail.item_id')
            ->when($request->item_id, fn($q,$v)=>$q->where('ail.item_id',$v))
            ->when($request->customer_id, fn($q,$v)=>$q->where('ai.customer_id',$v))
            ->groupBy('items.id','items.item_code','items.name','items.unit')
            ->selectRaw('items.id as item_id, items.item_code, items.name as item_name, items.unit,
                         SUM(ail.qty) as total_qty, SUM(ail.subtotal) as total_revenue')
            ->get();

        // Merge and re-aggregate by item_id
        $merged = collect($fromSales)->merge($fromAr)
            ->groupBy('item_id')
            ->map(function($group) {
                $first = $group->first();
                return (object)[
                    'item_id'       => $first->item_id,
                    'item_code'     => $first->item_code,
                    'item_name'     => $first->item_name,
                    'unit'          => $first->unit,
                    'total_qty'     => $group->sum('total_qty'),
                    'total_revenue' => $group->sum('total_revenue'),
                ];
            })
            ->values()
            ->sortByDesc('total_qty');

        $grandQty     = $merged->sum('total_qty');
        $grandRevenue = $merged->sum('total_revenue');
        $topItems     = $merged->take(5);

        $items     = DB::table('items')->whereNull('deleted_at')->orderBy('name')->get();
        $customers = DB::table('customers')->whereNull('deleted_at')->orderBy('name')->get();

        return view('reports.sold-products', compact('merged','grandQty','grandRevenue','topItems','from','to','items','customers'));
    }

    public function stockOpname(Request $request)
    {
        $to = $request->input('date_to', now()->toDateString());

        $rows = DB::table('items')
            ->leftJoin('inventory_movements as im', function($join) use ($to) {
                $join->on('im.item_id', '=', 'items.id')
                     ->where('im.date', '<=', $to);
            })
            ->whereNull('items.deleted_at')
            ->when($request->item_id, fn($q,$v)=>$q->where('items.id',$v))
            ->when($request->category, fn($q,$v)=>$q->where('items.category',$v))
            ->groupBy('items.id','items.item_code','items.name','items.unit','items.category','items.buy_price')
            ->selectRaw("
                items.id as item_id,
                items.item_code,
                items.name as item_name,
                items.unit,
                items.category,
                items.buy_price,
                COALESCE(SUM(CASE WHEN im.type = 'in' THEN im.qty ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN im.type = 'out' THEN im.qty ELSE 0 END), 0) as total_out,
                COALESCE(SUM(CASE WHEN im.type = 'adjustment' THEN im.qty ELSE 0 END), 0) as total_adj,
                COALESCE(SUM(CASE WHEN im.type = 'out' THEN -im.qty ELSE im.qty END), 0) as stock_current
            ")
            ->orderBy('items.item_code')
            ->get()
            ->map(function($row) {
                $row->stock_value = $row->stock_current * $row->buy_price;
                return $row;
            });

        $totalItems  = $rows->count();
        $totalValue  = $rows->sum('stock_value');
        $emptyStock  = $rows->where('stock_current', '<=', 0)->count();

        $items      = DB::table('items')->whereNull('deleted_at')->orderBy('name')->get();
        $categories = DB::table('items')->whereNull('deleted_at')->whereNotNull('category')->distinct()->pluck('category');

        return view('reports.stock-opname', compact('rows','to','totalItems','totalValue','emptyStock','items','categories'));
    }

    public function pajak(Request $request)
    {
        $from       = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to         = $request->input('date_to',   now()->toDateString());
        $sourceType = $request->input('source_type', 'all');

        $rows = collect();

        if ($sourceType === 'all' || $sourceType === 'sales_invoice') {
            $sales = DB::table('sales_invoices as si')
                ->join('customers as c', 'c.id', '=', 'si.customer_id')
                ->whereIn('si.status', ['posted','partial','paid'])
                ->where('si.tax_amount', '>', 0)
                ->whereBetween('si.date', [$from, $to])
                ->whereNull('si.deleted_at')
                ->selectRaw("si.invoice_number, 'Faktur Penjualan' as source, c.name as customer_name, si.date, si.subtotal, si.tax_amount, si.total")
                ->orderBy('si.date')
                ->get();
            $rows = $rows->merge($sales);
        }

        if ($sourceType === 'all' || $sourceType === 'ar_invoice') {
            $ar = DB::table('ar_invoices as ai')
                ->join('customers as c', 'c.id', '=', 'ai.customer_id')
                ->whereIn('ai.status', ['sent','partial','paid'])
                ->where('ai.tax_amount', '>', 0)
                ->whereBetween('ai.date', [$from, $to])
                ->whereNull('ai.deleted_at')
                ->selectRaw("ai.invoice_number, 'Invoice AR' as source, c.name as customer_name, ai.date, ai.subtotal, ai.tax_amount, ai.total")
                ->orderBy('ai.date')
                ->get();
            $rows = $rows->merge($ar);
        }

        $rows = $rows->sortBy('date')->values();

        $totalSubtotal = $rows->sum('subtotal');
        $totalTax      = $rows->sum('tax_amount');
        $totalWithTax  = $rows->sum('total');

        return view('reports.pajak', compact('rows','from','to','totalSubtotal','totalTax','totalWithTax','sourceType'));
    }

    public function paymentMethodsReport(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->input('date_to',   now()->toDateString());

        $salesReceipts = DB::table('sales_receipts as sr')
            ->join('payment_methods as pm', 'pm.id', '=', 'sr.payment_method_id')
            ->whereBetween('sr.date', [$from, $to])
            ->whereNull('sr.deleted_at')
            ->when($request->payment_method_id, fn($q,$v)=>$q->where('sr.payment_method_id',$v))
            ->groupBy('pm.id','pm.name','pm.type')
            ->selectRaw('pm.id as pm_id, pm.name as pm_name, pm.type as pm_type, COUNT(*) as trx_count, SUM(sr.amount) as total')
            ->get()->map(fn($r)=>array_merge((array)$r, ['channel'=>'Penerimaan Penjualan']));

        $arPayments = DB::table('ar_payments as ap')
            ->join('payment_methods as pm', 'pm.id', '=', 'ap.payment_method_id')
            ->whereBetween('ap.date', [$from, $to])
            ->when($request->payment_method_id, fn($q,$v)=>$q->where('ap.payment_method_id',$v))
            ->groupBy('pm.id','pm.name','pm.type')
            ->selectRaw('pm.id as pm_id, pm.name as pm_name, pm.type as pm_type, COUNT(*) as trx_count, SUM(ap.amount) as total')
            ->get()->map(fn($r)=>array_merge((array)$r, ['channel'=>'Penerimaan AR']));

        $apPayments = DB::table('ap_payments as app')
            ->join('payment_methods as pm', 'pm.id', '=', 'app.payment_method_id')
            ->whereBetween('app.date', [$from, $to])
            ->when($request->payment_method_id, fn($q,$v)=>$q->where('app.payment_method_id',$v))
            ->groupBy('pm.id','pm.name','pm.type')
            ->selectRaw('pm.id as pm_id, pm.name as pm_name, pm.type as pm_type, COUNT(*) as trx_count, SUM(app.amount) as total')
            ->get()->map(fn($r)=>array_merge((array)$r, ['channel'=>'Pembayaran AP']));

        $detail = collect($salesReceipts)->merge($arPayments)->merge($apPayments)->map(fn($r)=>(object)$r);

        // Build summary per payment method
        $summary = $detail->groupBy('pm_id')->map(function($group) {
            $first    = $group->first();
            $received = $group->whereIn('channel', ['Penerimaan Penjualan','Penerimaan AR'])->sum('total');
            $disbursed= $group->where('channel','Pembayaran AP')->sum('total');
            return (object)[
                'pm_id'      => $first->pm_id,
                'pm_name'    => $first->pm_name,
                'pm_type'    => $first->pm_type,
                'trx_count'  => $group->sum('trx_count'),
                'received'   => $received,
                'disbursed'  => $disbursed,
                'net'        => $received - $disbursed,
            ];
        })->values()->sortByDesc('received');

        $grandReceived  = $summary->sum('received');
        $grandDisbursed = $summary->sum('disbursed');
        $grandTrx       = $summary->sum('trx_count');

        $paymentMethods = DB::table('payment_methods')->orderBy('name')->get();

        return view('reports.payment-methods-report', compact('summary','detail','from','to','grandReceived','grandDisbursed','grandTrx','paymentMethods'));
    }

    public function customerSpent(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->input('date_to',   now()->toDateString());

        $fromSales = DB::table('sales_invoices as si')
            ->join('customers as c', 'c.id', '=', 'si.customer_id')
            ->whereIn('si.status', ['posted','partial','paid'])
            ->whereBetween('si.date', [$from, $to])
            ->whereNull('si.deleted_at')
            ->when($request->customer_id, fn($q,$v)=>$q->where('si.customer_id',$v))
            ->groupBy('c.id','c.customer_code','c.name','c.address')
            ->selectRaw("c.id as customer_id, c.customer_code, c.name as customer_name, c.address,
                         COUNT(si.id) as invoice_count,
                         SUM(si.total) as total_billed,
                         SUM(CASE WHEN si.status='paid' THEN si.total ELSE 0 END) as total_paid_full")
            ->get();

        $fromAr = DB::table('ar_invoices as ai')
            ->join('customers as c', 'c.id', '=', 'ai.customer_id')
            ->whereIn('ai.status', ['sent','partial','paid'])
            ->whereBetween('ai.date', [$from, $to])
            ->whereNull('ai.deleted_at')
            ->when($request->customer_id, fn($q,$v)=>$q->where('ai.customer_id',$v))
            ->groupBy('c.id','c.customer_code','c.name','c.address')
            ->selectRaw("c.id as customer_id, c.customer_code, c.name as customer_name, c.address,
                         COUNT(ai.id) as invoice_count,
                         SUM(ai.total) as total_billed,
                         SUM(CASE WHEN ai.status='paid' THEN ai.total ELSE 0 END) as total_paid_full")
            ->get();

        // Get actual payments from receipts and ar_payments for accurate paid amount
        $salesPaid = DB::table('sales_receipts as sr')
            ->join('sales_invoices as si', 'si.id', '=', 'sr.sales_invoice_id')
            ->whereBetween('si.date', [$from, $to])
            ->whereNull('sr.deleted_at')
            ->groupBy('sr.customer_id')
            ->selectRaw('sr.customer_id, SUM(sr.amount) as paid_amount')
            ->pluck('paid_amount','customer_id');

        $arPaid = DB::table('ar_payments')
            ->join('ar_invoices', 'ar_invoices.id', '=', 'ar_payments.ar_invoice_id')
            ->whereBetween('ar_invoices.date', [$from, $to])
            ->groupBy('ar_payments.customer_id')
            ->selectRaw('ar_payments.customer_id, SUM(ar_payments.amount) as paid_amount')
            ->pluck('paid_amount','customer_id');

        $merged = collect($fromSales)->merge($fromAr)
            ->groupBy('customer_id')
            ->map(function($group, $custId) use ($salesPaid, $arPaid) {
                $first = $group->first();
                $billed = $group->sum('total_billed');
                $paid   = ($salesPaid[$custId] ?? 0) + ($arPaid[$custId] ?? 0);
                return (object)[
                    'customer_id'   => $custId,
                    'customer_code' => $first->customer_code,
                    'customer_name' => $first->customer_name,
                    'address'       => $first->address,
                    'invoice_count' => $group->sum('invoice_count'),
                    'total_billed'  => $billed,
                    'total_paid'    => $paid,
                    'outstanding'   => max(0, $billed - $paid),
                ];
            })
            ->values()
            ->sortByDesc('total_billed');

        $grandBilled      = $merged->sum('total_billed');
        $grandPaid        = $merged->sum('total_paid');
        $grandOutstanding = $merged->sum('outstanding');
        $customers        = DB::table('customers')->whereNull('deleted_at')->orderBy('name')->get();

        return view('reports.customer-spent', compact('merged','from','to','grandBilled','grandPaid','grandOutstanding','customers'));
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
