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
        Schema::create('m_occupants', function (Blueprint $table) {
            $table->id('occupant_id');
            $table->string('occupant_name');
            $table->string('occupant_ktp_photo');
            $table->enum('occupant_status', ['tetap', 'kontrak']);
            $table->string('occupant_phone_number');
            $table->boolean('is_married')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_occupants');
    }
};
