<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->index('price');
            $table->index('created_at');
            
            $table->index(['category_id', 'price'], 'idx_cat_price');
            $table->index(['category_id', 'is_available', 'price'], 'idx_cat_avail_price');
            $table->index(['category_id', 'is_available', 'created_at'], 'idx_cat_avail_created');
            $table->index(['is_available', 'created_at'], 'idx_avail_created');
            
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
