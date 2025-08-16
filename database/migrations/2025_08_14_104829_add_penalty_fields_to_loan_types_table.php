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
        Schema::table('loan_types', function (Blueprint $table) {
            $table->enum('penalty_type', ['flat_fee', 'percentage'])->default('flat_fee')->after('max_term');
            $table->decimal('penalty_amount', 15, 2)->default(0)->after('penalty_type');
            $table->integer('prepayment_penalty_period')->nullable()->after('penalty_amount');
            $table->decimal('prepayment_penalty_amount', 15, 2)->nullable()->after('prepayment_penalty_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_types', function (Blueprint $table) {
            $table->dropColumn([
                'penalty_type',
                'penalty_amount',
                'prepayment_penalty_period',
                'prepayment_penalty_amount'
            ]);
        });
    }
};
