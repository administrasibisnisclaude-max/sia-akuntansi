<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = [
        'journal_id', 'account_id', 'description', 'debit', 'credit', 'order',
    ];
    protected $casts = ['debit' => 'decimal:2', 'credit' => 'decimal:2'];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
