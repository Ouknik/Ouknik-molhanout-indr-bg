<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('delivery_address');
            $table->decimal('delivery_latitude', 10, 8);
            $table->decimal('delivery_longitude', 11, 8);
            $table->dateTime('preferred_delivery_time')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'draft',
                'published',
                'receiving_offers',
                'accepted',
                'preparing',
                'on_delivery',
                'delivered',
                'cancelled'
            ])->default('draft');
            $table->foreignId('accepted_offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('confirmation_pin', 6)->nullable();
            $table->string('qr_code')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('offer_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['shop_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['delivery_latitude', 'delivery_longitude']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
