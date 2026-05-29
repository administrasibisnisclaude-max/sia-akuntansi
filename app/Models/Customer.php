<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_code', 'name', 'email', 'phone', 'address', 'ar_account_id', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function arAccount()
    {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function invoices()
    {
        return $this->hasMany(ArInvoice::class);
    }
}
