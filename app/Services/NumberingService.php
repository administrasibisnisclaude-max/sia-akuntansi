<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberingService
{
    public static function generate(string $prefix, string $table, string $column): string
    {
        $ym = now()->format('Ym');
        $like = "{$prefix}-{$ym}-%";
        $last = DB::table($table)
            ->where($column, 'like', $like)
            ->orderByDesc($column)
            ->value($column);

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('%s-%s-%04d', $prefix, $ym, $seq);
    }
}
