<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number', 'ar_invoice_id', 'customer_id', 'payment_method_id',
        'date', 'amount', 'notes', 'journal_id', 'created_by',
    ];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];

    public function invoice()
    {
        return $this->belongsTo(ArInvoice::class, 'ar_invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
