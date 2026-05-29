<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'item_id', 'type', 'qty', 'unit_cost', 'reference_type', 'reference_id',
        'date', 'notes', 'created_by',
    ];
    protected $casts = ['date' => 'date', 'qty' => 'decimal:2', 'unit_cost' => 'decimal:2'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
