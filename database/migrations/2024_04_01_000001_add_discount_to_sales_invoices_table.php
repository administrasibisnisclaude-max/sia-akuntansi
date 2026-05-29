<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales_invoices', 'discount_amount')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
