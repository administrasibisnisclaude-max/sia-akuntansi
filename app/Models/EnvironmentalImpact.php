<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentalImpact extends Model
{
    protected $fillable = [
        'item_id', 'reference_type', 'reference_id', 'date',
        'qty', 'waste_kg', 'carbon_kg', 'waste_category', 'carbon_category', 'created_by',
    ];

    protected $casts = [
        'date'     => 'date',
        'qty'      => 'decimal:2',
        'waste_kg' => 'decimal:4',
        'carbon_kg'=> 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
