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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // seller (owner of the store)
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // store category
            $table->text('country')->nullable(); // store country
            $table->text('region')->nullable(); // store region
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('image')->nullable(); // store logo / banner
            $table->boolean('is_online')->default(true); // toggle: online/offline
            $table->time('open_time')->nullable(); // daily opening time
            $table->time('closing_time')->nullable(); // daily closing time
            $table->string('store_link')->nullable(); // store URL or identifier
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
