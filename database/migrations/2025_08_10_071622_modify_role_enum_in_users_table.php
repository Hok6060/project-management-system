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
        Schema::table('users', function (Blueprint $table) {
            // Modify the ENUM column to add the new roles
            $table->enum('role', [
                'admin',
                'project_manager',
                'team_member',
                'client',
                'loan_officer', // New
                'borrower'      // New
            ])->default('team_member')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to the old list if we roll back
             $table->enum('role', [
                'admin',
                'project_manager',
                'team_member',
                'client'
            ])->default('team_member')->change();
        });
    }
};
