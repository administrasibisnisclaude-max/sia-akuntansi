<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\{Item, InventoryMovement};
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function index()
    {
        $items = Item::where('type', 'product')->where('is_active', true)->orderBy('name')->get();
        foreach ($items as $item) {
            $item->stock = $item->currentStock();
        }
        return view('inventory.index', compact('items'));
    }

    public function movements(Request $request)
    {
        $query = InventoryMovement::with('item')
            ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
            ->latest('date');

        $movements = $query->paginate(30);
        $items = Item::where('type', 'product')->orderBy('name')->get();
        return view('inventory.movements', compact('movements', 'items'));
    }

    public function adjustment(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'qty'     => 'required|numeric',
            'notes'   => 'nullable|string',
            'date'    => 'required|date',
        ]);

        InventoryMovement::create([
            'item_id'    => $request->item_id,
            'type'       => 'adjustment',
            'qty'        => $request->qty,
            'unit_cost'  => 0,
            'date'       => $request->date,
            'notes'      => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Penyesuaian stok berhasil dicatat.');
    }
}
