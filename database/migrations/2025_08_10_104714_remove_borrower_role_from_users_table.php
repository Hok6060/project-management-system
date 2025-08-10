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
        $table->enum('role', [
            'admin',
            'project_manager',
            'team_member',
            'client',
            'loan_officer' // Borrower is removed
        ])->default('team_member')->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'project_manager',
                'team_member',
                'client',
                'loan_officer',
                'borrower' // Add it back on rollback
            ])->default('team_member')->change();
        });
    }
};
