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

            // Add the new calculation type column
            $table->enum('calculation_type', [
                'flat_interest',
                'declining_balance',
                'interest_only'
            ])->default('declining_balance');

            $table->decimal('min_interest_rate', 5, 2);
            $table->decimal('max_interest_rate', 5, 2);
            $table->integer('min_term'); // Loan term in months
            $table->integer('max_term'); // Loan term in months
            $table->boolean('is_active')->default(true);
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
