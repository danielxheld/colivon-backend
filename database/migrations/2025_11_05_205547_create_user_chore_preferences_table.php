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
        Schema::create('user_chore_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('chore_id')->constrained()->onDelete('cascade');
            $table->enum('preference', ['love', 'like', 'neutral', 'dislike', 'hate'])->default('neutral');
            $table->decimal('weight', 3, 2)->default(1.00); // 0.00 to 2.00, affects roulette probability
            $table->timestamps();

            // Unique constraint - one preference per user per chore
            $table->unique(['user_id', 'chore_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_chore_preferences');
    }
};
