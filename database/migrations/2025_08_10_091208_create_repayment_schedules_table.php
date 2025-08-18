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
        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->integer('payment_number');
            $table->date('due_date');
            $table->decimal('payment_amount', 15, 2);
            $table->decimal('principal_component', 15, 2);
            $table->decimal('interest_component', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', [
                'pending',
                'due',
                'late',
                'paid',
                'paid_late',
                'partially_paid'
            ])->default('pending');
            $table->date('paid_on')->nullable();
            $table->date('last_penalty_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayment_schedules');
    }
};
