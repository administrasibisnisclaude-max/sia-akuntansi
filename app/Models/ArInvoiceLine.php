<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArInvoiceLine extends Model
{
    protected $fillable = [
        'ar_invoice_id', 'item_id', 'description', 'qty', 'unit_price', 'tax_rate', 'subtotal',
    ];
    protected $casts = ['qty' => 'decimal:2', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function invoice()
    {
        return $this->belongsTo(ArInvoice::class, 'ar_invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
