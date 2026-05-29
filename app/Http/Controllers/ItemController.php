<?php

namespace App\Http\Controllers;

use App\Models\{Item, Account};
use App\Services\NumberingService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('name')->paginate(20);
        foreach ($items as $item) {
            $item->stock = $item->currentStock();
        }
        return view('items.index', compact('items'));
    }

    public function create()
    {
        $accounts = Account::orderBy('account_code')->get();
        return view('items.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'unit'                 => 'required|string|max:20',
            'type'                 => 'required|in:product,service',
            'buy_price'            => 'required|numeric|min:0',
            'sell_price'           => 'required|numeric|min:0',
            'category'             => 'nullable|string|max:50',
            'cogs_account_id'      => 'nullable|exists:accounts,id',
            'sales_account_id'     => 'nullable|exists:accounts,id',
            'purchase_account_id'  => 'nullable|exists:accounts,id',
            'inventory_account_id' => 'nullable|exists:accounts,id',
            'is_active'            => 'boolean',
        ]);
        $data['item_code'] = NumberingService::generate('ITM', 'items', 'item_code');
        $data['is_active'] = $request->boolean('is_active', true);
        Item::create($data);
        return redirect()->route('items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $accounts = Account::orderBy('account_code')->get();
        return view('items.edit', compact('item', 'accounts'));
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'unit'                 => 'required|string|max:20',
            'type'                 => 'required|in:product,service',
            'buy_price'            => 'required|numeric|min:0',
            'sell_price'           => 'required|numeric|min:0',
            'category'             => 'nullable|string|max:50',
            'cogs_account_id'      => 'nullable|exists:accounts,id',
            'sales_account_id'     => 'nullable|exists:accounts,id',
            'purchase_account_id'  => 'nullable|exists:accounts,id',
            'inventory_account_id' => 'nullable|exists:accounts,id',
            'is_active'            => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $item->update($data);
        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }
}
