<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    protected $fillable = [
        'transaction_number', 'cashier_id', 'date',
        'subtotal', 'discount_amount', 'tax_percent', 'tax_amount', 'total',
        'payment_method', 'paid_amount', 'change_amount',
        'notes', 'status', 'journal_id',
    ];

    protected $casts = [
        'date'            => 'date',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent'     => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    public function cashier()  { return $this->belongsTo(User::class, 'cashier_id'); }
    public function journal()  { return $this->belongsTo(Journal::class); }
    public function lines()    { return $this->hasMany(PosTransactionLine::class); }

    public function paymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'tunai'    => 'Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer Bank',
            'ewallet'  => 'E-Wallet',
            default    => ucfirst($this->payment_method),
        };
    }
}
