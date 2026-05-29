<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'customer_id', 'date', 'due_date', 'status',
        'subtotal', 'tax_amount', 'total', 'notes', 'journal_id', 'created_by',
    ];
    protected $casts = ['date' => 'date', 'due_date' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines()
    {
        return $this->hasMany(ArInvoiceLine::class);
    }

    public function payments()
    {
        return $this->hasMany(ArPayment::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return (float) $this->total - $this->paidAmount();
    }
}
