<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_code', 'name', 'unit', 'type', 'buy_price', 'sell_price', 'category',
        'cogs_account_id', 'sales_account_id', 'purchase_account_id', 'inventory_account_id', 'is_active',
        'waste_per_unit', 'carbon_per_unit', 'waste_category', 'carbon_category', 'environmental_notes',
    ];
    protected $casts = [
        'is_active'      => 'boolean',
        'buy_price'      => 'decimal:2',
        'sell_price'     => 'decimal:2',
        'waste_per_unit' => 'decimal:4',
        'carbon_per_unit'=> 'decimal:4',
    ];

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function currentStock(): float
    {
        $in  = $this->movements()->where('type', 'in')->sum('qty');
        $adj = $this->movements()->where('type', 'adjustment')->sum('qty');
        $out = $this->movements()->where('type', 'out')->sum('qty');
        return (float) ($in + $adj - $out);
    }
}
