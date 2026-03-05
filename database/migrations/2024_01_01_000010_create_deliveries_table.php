<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['preparing', 'on_the_way', 'delivered', 'failed'])->default('preparing');
            $table->string('confirmation_pin', 6)->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['distributor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
