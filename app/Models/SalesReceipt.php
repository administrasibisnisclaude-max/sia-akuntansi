<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'receipt_number', 'sales_invoice_id', 'customer_id', 'payment_method_id',
        'date', 'amount', 'reference_number', 'notes', 'journal_id', 'created_by',
    ];
    protected $casts = ['date' => 'date'];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
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
