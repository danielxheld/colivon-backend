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
        Schema::create('chores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('recurrence_type', ['daily', 'weekly', 'biweekly', 'monthly', 'custom', 'once'])->default('weekly');
            $table->integer('recurrence_interval')->nullable(); // For custom recurrence (in days)
            $table->integer('difficulty_points')->default(1); // 1-5, affects XP gained
            $table->integer('estimated_duration')->nullable(); // In minutes
            $table->boolean('requires_photo')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('assignment_mode', ['auto', 'manual', 'roulette'])->default('manual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chores');
    }
};
