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
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique()->nullable();
            $table->string('name');
            $table->string('status')->default('queued'); // e.g., queued, running, completed, failed
            $table->integer('progress')->default(0); // A percentage from 0 to 100
            $table->json('details')->nullable(); // To store progress of sub-tasks
            $table->text('output')->nullable(); // To store any final messages or errors
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_statuses');
    }
};
