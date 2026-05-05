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
        Schema::create('r_house_occupants', function (Blueprint $table) {
            $table->id('house_occupant_id');
            $table->unsignedBigInteger('house_id');
            $table->unsignedBigInteger('occupant_id');
            $table->date('start_in_date');
            $table->date('end_in_date')->nullable()->default(null);
            $table->boolean('is_current')->default(true);
            $table->boolean('is_head_family')->default(false);

            $table->foreign('house_id')->references('house_id')->on('m_houses')->onDelete('cascade');
            $table->foreign('occupant_id')->references('occupant_id')->on('m_occupants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_house_occupants');
    }
};
