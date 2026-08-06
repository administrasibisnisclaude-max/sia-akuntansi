<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransactionLine extends Model
{
    protected $fillable = [
        'pos_transaction_id', 'item_id', 'description',
        'qty', 'unit_price', 'discount_percent', 'subtotal',
    ];

    protected $casts = [
        'qty'              => 'decimal:2',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    public function item() { return $this->belongsTo(Item::class); }
    public function pos()  { return $this->belongsTo(PosTransaction::class, 'pos_transaction_id'); }
}
