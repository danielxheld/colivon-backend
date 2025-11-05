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
        Schema::create('chore_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chore_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('due_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->enum('assigned_by', ['manual', 'auto', 'roulette'])->default('manual');
            $table->timestamps();

            // Index for quick lookups
            $table->index(['user_id', 'status']);
            $table->index(['chore_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chore_assignments');
    }
};
