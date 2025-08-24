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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // link payment to order
            $table->string('session_id')->index(); // buyer info
            $table->foreignId('store_id')->nullable()->constrained()->onDelete('cascade'); // optional if multi-store
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['card', 'mobile_money',  'bank_transfer']); // extendable
            $table->string('transaction_id')->nullable(); // from gateway
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->text('notes')->nullable(); // optional info from system/admin
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
