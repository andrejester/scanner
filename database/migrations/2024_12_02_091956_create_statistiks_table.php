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
        if (!Schema::hasTable('statistiks')) {
            Schema::create('statistiks', function (Blueprint $table) {
                $table->id();
                $table->string('ip');
                $table->date('tanggal');
                $table->integer('hits')->default(1);
                $table->timestamp('online')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistiks');
    }
};
