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
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // which store submitted
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade'); // link to document type
            $table->string('document_path'); // path or URL to uploaded file
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // verification status
            $table->text('remarks')->nullable(); // admin notes or rejection reason
            $table->timestamp('verified_at')->nullable(); // when verified
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
