<?php

namespace App\Services;

use App\Models\{Item, InventoryMovement};

class InventoryService
{
    public static function recordIn(Item $item, float $qty, float $unitCost, string $refType, int $refId, int $userId): InventoryMovement
    {
        return InventoryMovement::create([
            'item_id'        => $item->id,
            'type'           => 'in',
            'qty'            => $qty,
            'unit_cost'      => $unitCost,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'date'           => now()->toDateString(),
            'created_by'     => $userId,
        ]);
    }

    public static function recordOut(Item $item, float $qty, string $refType, int $refId, int $userId): InventoryMovement
    {
        return InventoryMovement::create([
            'item_id'        => $item->id,
            'type'           => 'out',
            'qty'            => $qty,
            'unit_cost'      => $item->buy_price,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'date'           => now()->toDateString(),
            'created_by'     => $userId,
        ]);
    }
}
