<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_code', 'name', 'type', 'normal_balance', 'parent_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getBalanceForPeriod(?string $from = null, ?string $to = null): array
    {
        $query = $this->journalLines()
            ->whereHas('journal', fn($q) => $q->where('status', 'posted')
                ->when($from, fn($q) => $q->where('date', '>=', $from))
                ->when($to, fn($q) => $q->where('date', '<=', $to)));

        return [
            'debit'  => $query->sum('debit'),
            'credit' => $query->sum('credit'),
        ];
    }
}
