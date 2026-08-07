<?php

namespace App\Http\Controllers;

use App\Models\{Item, PosTransaction, PosTransactionLine};
use App\Services\{EnvironmentalService, InventoryService, JournalService, NumberingService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $items = Item::where('is_active', true)
            ->withSum(['movements as stock_in'  => fn($m) => $m->where('type', 'in')], 'qty')
            ->withSum(['movements as stock_adj' => fn($m) => $m->where('type', 'adjustment')], 'qty')
            ->withSum(['movements as stock_out' => fn($m) => $m->where('type', 'out')], 'qty')
            ->select('id', 'item_code', 'name', 'sell_price', 'unit', 'type')
            ->orderBy('name')
            ->get()
            ->map(fn($item) => [
                'id'         => $item->id,
                'item_code'  => $item->item_code,
                'name'       => $item->name,
                'sell_price' => (float) $item->sell_price,
                'unit'       => $item->unit,
                'type'       => $item->type,
                'stock'      => $item->type === 'product'
                    ? (float) (($item->stock_in ?? 0) + ($item->stock_adj ?? 0) - ($item->stock_out ?? 0))
                    : null,
            ]);

        return view('pos.index', compact('items'));
    }

    public function searchItems(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $items = Item::where('is_active', true)
            ->where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('item_code', 'like', "%{$q}%"))
            ->withSum(['movements as stock_in'  => fn($m) => $m->where('type', 'in')], 'qty')
            ->withSum(['movements as stock_adj' => fn($m) => $m->where('type', 'adjustment')], 'qty')
            ->withSum(['movements as stock_out' => fn($m) => $m->where('type', 'out')], 'qty')
            ->select('id', 'item_code', 'name', 'sell_price', 'unit', 'type')
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn($item) => [
                'id'         => $item->id,
                'item_code'  => $item->item_code,
                'name'       => $item->name,
                'sell_price' => (float) $item->sell_price,
                'unit'       => $item->unit,
                'type'       => $item->type,
                'stock'      => $item->type === 'product'
                    ? (float) (($item->stock_in ?? 0) + ($item->stock_adj ?? 0) - ($item->stock_out ?? 0))
                    : null,
            ]);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.item_id'          => 'nullable|exists:items,id',
            'items.*.description'      => 'required|string|max:200',
            'items.*.qty'              => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_percent' => 'required|numeric|min:0|max:100',
            'items.*.subtotal'         => 'required|numeric|min:0',
            'subtotal'                 => 'required|numeric|min:0',
            'discount_amount'          => 'required|numeric|min:0',
            'tax_percent'              => 'required|numeric|min:0|max:100',
            'tax_amount'               => 'required|numeric|min:0',
            'total'                    => 'required|numeric|min:0.01',
            'payment_method'           => 'required|in:tunai,qris,transfer,ewallet',
            'paid_amount'              => 'required|numeric|min:0',
            'change_amount'            => 'required|numeric|min:0',
            'notes'                    => 'nullable|string|max:500',
        ]);

        $transaction = DB::transaction(function () use ($data) {
            $pos = PosTransaction::create([
                'transaction_number' => NumberingService::generate('POS', 'pos_transactions', 'transaction_number'),
                'cashier_id'         => auth()->id(),
                'date'               => now()->toDateString(),
                'subtotal'           => $data['subtotal'],
                'discount_amount'    => $data['discount_amount'],
                'tax_percent'        => $data['tax_percent'],
                'tax_amount'         => $data['tax_amount'],
                'total'              => $data['total'],
                'payment_method'     => $data['payment_method'],
                'paid_amount'        => $data['paid_amount'],
                'change_amount'      => $data['change_amount'],
                'notes'              => $data['notes'] ?? null,
                'status'             => 'completed',
            ]);

            foreach ($data['items'] as $lineData) {
                $line = PosTransactionLine::create([
                    'pos_transaction_id' => $pos->id,
                    'item_id'            => $lineData['item_id'] ?? null,
                    'description'        => $lineData['description'],
                    'qty'                => $lineData['qty'],
                    'unit_price'         => $lineData['unit_price'],
                    'discount_percent'   => $lineData['discount_percent'],
                    'subtotal'           => $lineData['subtotal'],
                ]);

                if (!empty($lineData['item_id'])) {
                    $item = Item::find($lineData['item_id']);
                    if ($item && $item->type === 'product') {
                        InventoryService::recordOut(
                            $item, $lineData['qty'],
                            'pos_transaction_line', $line->id,
                            auth()->id()
                        );
                    }
                }
            }

            $pos->load('lines.item');

            try {
                $journal = JournalService::createFromPos($pos, auth()->id());
                $pos->update(['journal_id' => $journal->id]);
            } catch (\Exception $e) {
                // Journal creation failed (missing accounts) — transaction still saved
            }

            EnvironmentalService::recordFromLines(
                $pos->lines,
                'pos_transaction_line',
                $pos->date->toDateString(),
                auth()->id()
            );

            return $pos;
        });

        return response()->json([
            'success' => true,
            'id'      => $transaction->id,
            'number'  => $transaction->transaction_number,
        ]);
    }

    public function receipt(PosTransaction $transaction)
    {
        $transaction->load('lines.item', 'cashier');
        return view('pos.receipt', compact('transaction'));
    }

    public function history(Request $request)
    {
        $transactions = PosTransaction::with('cashier')
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest()
            ->paginate(20);

        $todayTotal = PosTransaction::whereDate('date', today())
            ->where('status', 'completed')
            ->sum('total');

        return view('pos.history', compact('transactions', 'todayTotal'));
    }
}
