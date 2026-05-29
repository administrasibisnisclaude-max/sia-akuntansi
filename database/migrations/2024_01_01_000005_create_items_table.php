<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 20)->unique();
            $table->string('name', 100);
            $table->string('unit', 20)->default('pcs');
            $table->enum('type', ['product', 'service'])->default('product');
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->string('category', 50)->nullable();
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('purchase_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
