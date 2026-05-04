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
        Schema::create('t_payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('dues_type_id');
            $table->unsignedBigInteger('payer_occupant_id');
            $table->unsignedBigInteger('house_occupant_id');
            $table->decimal('payment_amount', 15, 2);
            $table->date('payment_date');
            $table->integer('payment_period_month');
            $table->integer('payment_period_year');
            $table->enum('payment_status', ['pending', 'success', 'rejected'])->nullable()->default(null);

            $table->foreign('dues_type_id')->references('dues_type_id')->on('m_dues_types')->onDelete('cascade');
            $table->foreign('payer_occupant_id')->references('occupant_id')->on('m_occupants')->onDelete('cascade');
            $table->foreign('house_occupant_id')->references('house_occupant_id')->on('r_house_occupants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_payments');
    }
};
