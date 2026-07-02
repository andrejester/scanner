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
        Schema::create('file_scanner_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 1000);
            $table->string('file_name');
            $table->bigInteger('file_size')->default(0);
            $table->string('file_hash', 64)->index(); // SHA-256 = 64 hex chars
            $table->enum('threat_level', ['safe', 'low', 'medium', 'high', 'critical'])->default('safe');
            $table->string('threat_type', 500)->nullable();
            $table->json('suspicious_patterns')->nullable();
            $table->enum('scan_result', ['clean', 'threat_detected', 'error'])->default('clean');
            $table->boolean('is_quarantined')->default(false);
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->foreign('scanned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_scanner_logs');
    }
};
