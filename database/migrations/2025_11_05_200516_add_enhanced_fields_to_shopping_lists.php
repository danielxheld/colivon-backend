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
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->string('store')->nullable()->after('is_public');
            $table->foreignId('currently_shopping_by_id')->nullable()->constrained('users')->onDelete('set null')->after('store');
            $table->boolean('is_template')->default(false)->after('currently_shopping_by_id');
            $table->string('template_name')->nullable()->after('is_template');
            $table->decimal('estimated_total', 10, 2)->nullable()->after('template_name');
            $table->decimal('actual_total', 10, 2)->nullable()->after('estimated_total');
            $table->timestamp('last_sync')->nullable()->after('actual_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->dropForeign(['currently_shopping_by_id']);
            $table->dropColumn(['store', 'currently_shopping_by_id', 'is_template', 'template_name', 'estimated_total', 'actual_total', 'last_sync']);
        });
    }
};
