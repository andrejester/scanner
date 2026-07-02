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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_slug')->unique();
            $table->string('company_email');
            $table->string('company_phone');
            $table->text('company_address');
            $table->text('company_maps')->nullable();
            $table->string('company_photo');
            $table->string('company_whatsapp')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_ig')->nullable();
            $table->string('company_admin')->nullable();
            $table->string('company_youtube')->nullable();
            $table->text('company_summary')->nullable();
            $table->text('company_deskripsi')->nullable();
            $table->text('company_visi')->nullable();
            $table->text('company_misi')->nullable();
            $table->text('company_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
