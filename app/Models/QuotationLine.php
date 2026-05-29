<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationLine extends Model
{
    protected $fillable = [
        'quotation_id', 'item_id', 'description', 'qty', 'unit_price', 'subtotal',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
