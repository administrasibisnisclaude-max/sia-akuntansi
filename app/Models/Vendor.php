<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_code', 'name', 'email', 'phone', 'address', 'ap_account_id', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function apAccount()
    {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function bills()
    {
        return $this->hasMany(ApBill::class);
    }
}
