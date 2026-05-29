<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_number', 'customer_id', 'date', 'valid_until', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'total', 'notes', 'created_by',
    ];
    protected $casts = ['date' => 'date', 'valid_until' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines()
    {
        return $this->hasMany(QuotationLine::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
