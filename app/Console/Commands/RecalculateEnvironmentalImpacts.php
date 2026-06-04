<?php

namespace App\Console\Commands;

use App\Models\EnvironmentalImpact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateEnvironmentalImpacts extends Command
{
    protected $signature   = 'environmental:recalculate {--dry-run : Preview without inserting}';
    protected $description = 'Backfill environmental impacts from posted sales and AR invoices';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $sources = [
            [
                'table'      => 'sales_invoice_lines',
                'join_table' => 'sales_invoices',
                'join_key'   => 'sales_invoice_id',
                'status_col' => 'sales_invoices.status',
                'statuses'   => ['posted'],
                'date_col'   => 'sales_invoices.date',
                'ref_type'   => 'sales_invoice_line',
            ],
            [
                'table'      => 'ar_invoice_lines',
                'join_table' => 'ar_invoices',
                'join_key'   => 'ar_invoice_id',
                'status_col' => 'ar_invoices.status',
                'statuses'   => ['sent', 'partial', 'paid'],
                'date_col'   => 'ar_invoices.date',
                'ref_type'   => 'ar_invoice_line',
            ],
        ];

        $inserted = 0;
        $skipped  = 0;

        foreach ($sources as $src) {
            $lines = DB::table($src['table'])
                ->join($src['join_table'], "{$src['join_table']}.id", '=', "{$src['table']}.{$src['join_key']}")
                ->join('items', 'items.id', '=', "{$src['table']}.item_id")
                ->whereIn($src['status_col'], $src['statuses'])
                ->whereNull("{$src['join_table']}.deleted_at")
                ->whereNull('items.deleted_at')
                ->where(fn($q) => $q->where('items.waste_per_unit', '>', 0)->orWhere('items.carbon_per_unit', '>', 0))
                ->where("{$src['table']}.qty", '>', 0)
                ->select(
                    "{$src['table']}.id as line_id",
                    "{$src['table']}.item_id",
                    "{$src['table']}.qty",
                    DB::raw("{$src['date_col']} as invoice_date"),
                    'items.waste_per_unit',
                    'items.carbon_per_unit',
                    'items.waste_category',
                    'items.carbon_category',
                )
                ->get();

            foreach ($lines as $line) {
                $exists = EnvironmentalImpact::where('reference_type', $src['ref_type'])
                    ->where('reference_id', $line->line_id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $this->line("  [{$src['ref_type']}] line #{$line->line_id} — waste: {$line->waste_per_unit} × {$line->qty}");

                if (!$dryRun) {
                    EnvironmentalImpact::create([
                        'item_id'        => $line->item_id,
                        'reference_type' => $src['ref_type'],
                        'reference_id'   => $line->line_id,
                        'date'           => $line->invoice_date,
                        'qty'            => $line->qty,
                        'waste_kg'       => $line->qty * $line->waste_per_unit,
                        'carbon_kg'      => $line->qty * $line->carbon_per_unit,
                        'waste_category' => $line->waste_category,
                        'carbon_category'=> $line->carbon_category,
                        'created_by'     => 1,
                    ]);
                }

                $inserted++;
            }
        }

        $action = $dryRun ? 'Would insert' : 'Inserted';
        $this->info("{$action} {$inserted} records. Skipped {$skipped} (already exist).");
        return self::SUCCESS;
    }
}
