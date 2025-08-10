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
        Schema::table('loans', function (Blueprint $table) {
            // 1. Drop the old foreign key constraint that points to the users table
            $table->dropForeign(['borrower_id']);

            // 2. Rename the column from borrower_id to customer_id
            $table->renameColumn('borrower_id', 'customer_id');

            // 3. Add a new foreign key constraint that points to the new customers table
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Revert all the changes if we roll back the migration
            $table->dropForeign(['customer_id']);
            $table->renameColumn('customer_id', 'borrower_id');
            $table->foreign('borrower_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
