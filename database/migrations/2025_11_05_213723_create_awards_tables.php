<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Awards definition table
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'bathroom_master', 'kitchen_king'
            $table->string('name'); // e.g., 'Bathroom Master'
            $table->string('description');
            $table->string('icon')->default('🏆'); // Emoji or icon identifier
            $table->string('category'); // e.g., 'bathroom', 'kitchen', 'general'
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->json('criteria'); // e.g., {"chore_completions": 10, "chore_category": "bathroom"}
            $table->timestamps();
        });

        // User awards (earned achievements)
        Schema::create('user_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->foreignId('award_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->integer('progress')->default(0); // For tracking progress towards award
            $table->timestamps();

            $table->unique(['user_id', 'household_id', 'award_id']);
            $table->index(['user_id', 'household_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_awards');
        Schema::dropIfExists('awards');
    }
};
