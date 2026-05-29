<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('waste_per_unit', 10, 4)->default(0)->after('unit');
            $table->decimal('carbon_per_unit', 10, 4)->default(0)->after('waste_per_unit');
            $table->string('waste_category', 50)->nullable()->after('carbon_per_unit');
            $table->string('carbon_category', 50)->nullable()->after('waste_category');
            $table->text('environmental_notes')->nullable()->after('carbon_category');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['waste_per_unit', 'carbon_per_unit', 'waste_category', 'carbon_category', 'environmental_notes']);
        });
    }
};
