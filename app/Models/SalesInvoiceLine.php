<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'item_id', 'description', 'qty', 'unit_price', 'subtotal',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
