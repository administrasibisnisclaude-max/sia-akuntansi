<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number', 'ap_bill_id', 'vendor_id', 'payment_method_id',
        'date', 'amount', 'notes', 'journal_id', 'created_by',
    ];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];

    public function bill()
    {
        return $this->belongsTo(ApBill::class, 'ap_bill_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
