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
        Schema::create('chore_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chore_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('completed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->useCurrent();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->integer('xp_earned')->default(0);
            $table->timestamps();

            // Index for user stats
            $table->index(['completed_by', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chore_completions');
    }
};
