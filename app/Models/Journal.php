<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'journal_number', 'date', 'description', 'status',
        'reference', 'reference_type', 'created_by', 'posted_by', 'posted_at',
    ];
    protected $casts = ['date' => 'date', 'posted_at' => 'datetime'];

    public function lines()
    {
        return $this->hasMany(JournalLine::class)->orderBy('order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function totalDebit(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit() - $this->totalCredit()) < 0.01;
    }
}
