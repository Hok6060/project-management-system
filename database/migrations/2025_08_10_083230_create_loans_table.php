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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_identifier')->unique();
            $table->foreignId('loan_type_id')->constrained('loan_types');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('loan_officer_id')->nullable()->constrained('users');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term');
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annually'])->default('monthly');
            $table->integer('interest_free_periods')->default(0);
            $table->enum('status', [
                'pending',
                'approved',
                'active',
                'rejected',
                'completed',
                'defaulted',
                'cancelled'
            ])->default('pending');
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('first_payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
