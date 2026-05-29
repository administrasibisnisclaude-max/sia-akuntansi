<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('environmental_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->date('date');
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('waste_kg', 12, 4)->default(0);
            $table->decimal('carbon_kg', 12, 4)->default(0);
            $table->string('waste_category', 50)->nullable();
            $table->string('carbon_category', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['date', 'item_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_impacts');
    }
};
