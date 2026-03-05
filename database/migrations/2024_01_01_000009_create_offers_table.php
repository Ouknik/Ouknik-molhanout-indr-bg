<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('delivery_cost', 10, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('estimated_delivery_time')->nullable();
            $table->dateTime('estimated_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['submitted', 'accepted', 'rejected', 'expired'])->default('submitted');
            $table->boolean('is_cheapest')->default(false);
            $table->boolean('is_fastest')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index(['distributor_id', 'status']);
            $table->unique(['order_id', 'distributor_id']);
        });

        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_items');
        Schema::dropIfExists('offers');
    }
};
