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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');  // ID Pengguna yang membuat catatan
            $table->text('message');    // Isi catatan
            $table->string('date');     // Tanggal catatan
            $table->integer('top');     // Posisi top dari catatan
            $table->integer('left');    // Posisi left dari catatan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
