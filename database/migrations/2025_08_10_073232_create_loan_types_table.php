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
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('calculation_type', [
                'flat_interest',
                'declining_balance',
                'interest_only'
            ])->default('declining_balance');
            $table->decimal('min_interest_rate', 5, 2);
            $table->decimal('max_interest_rate', 5, 2);
            $table->integer('min_term');
            $table->integer('max_term');
            $table->boolean('is_active')->default(true);
            $table->enum('penalty_type', ['flat_fee', 'percentage'])->default('flat_fee');
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->integer('prepayment_penalty_period')->nullable();
            $table->decimal('prepayment_penalty_amount', 15, 2)->nullable();
            $table->integer('grace_days')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
