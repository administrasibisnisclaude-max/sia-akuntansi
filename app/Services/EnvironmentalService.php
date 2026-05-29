<?php

namespace App\Services;

use App\Models\EnvironmentalImpact;

class EnvironmentalService
{
    public static function recordFromLines(iterable $lines, string $referenceType, string $date, int $userId): void
    {
        foreach ($lines as $line) {
            $item = $line->item;
            if (!$item) continue;
            if ($line->qty <= 0) continue;
            if ($item->waste_per_unit <= 0 && $item->carbon_per_unit <= 0) continue;

            EnvironmentalImpact::create([
                'item_id'        => $item->id,
                'reference_type' => $referenceType,
                'reference_id'   => $line->id,
                'date'           => $date,
                'qty'            => $line->qty,
                'waste_kg'       => $line->qty * $item->waste_per_unit,
                'carbon_kg'      => $line->qty * $item->carbon_per_unit,
                'waste_category' => $item->waste_category,
                'carbon_category'=> $item->carbon_category,
                'created_by'     => $userId,
            ]);
        }
    }
}
