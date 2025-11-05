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
        Schema::create('gamification_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->integer('total_xp')->default(0);
            $table->integer('level')->default(1);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->integer('total_chores_completed')->default(0);
            $table->integer('current_month_xp')->default(0);
            $table->integer('current_month_chores')->default(0);
            $table->string('title')->default('Neuling');
            $table->timestamps();

            // Unique constraint - one stat record per user per household
            $table->unique(['user_id', 'household_id']);
            // Index for leaderboard queries
            $table->index(['household_id', 'current_month_xp']);
            $table->index(['household_id', 'total_xp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_stats');
    }
};
