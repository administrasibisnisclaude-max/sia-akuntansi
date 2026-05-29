<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'type', 'account_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
