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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['assignee_id']);

            // Add the new foreign key constraint with onDelete('set null')
            $table->foreign('assignee_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert to the old constraint if we roll back
            $table->dropForeign(['assignee_id']);

            $table->foreign('assignee_id')
                  ->references('id')
                  ->on('users');
        });
    }
};
