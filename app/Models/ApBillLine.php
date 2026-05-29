<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApBillLine extends Model
{
    protected $fillable = [
        'ap_bill_id', 'item_id', 'description', 'qty', 'unit_price', 'tax_rate', 'subtotal',
    ];
    protected $casts = ['qty' => 'decimal:2', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function bill()
    {
        return $this->belongsTo(ApBill::class, 'ap_bill_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
