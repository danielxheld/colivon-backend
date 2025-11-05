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
        Schema::table('shopping_list_items', function (Blueprint $table) {
            $table->foreignId('claimed_by_id')->nullable()->after('is_recurring')->constrained('users')->nullOnDelete();
            $table->foreignId('bought_by_id')->nullable()->after('claimed_by_id')->constrained('users')->nullOnDelete();
            $table->decimal('actual_price', 10, 2)->nullable()->after('price');
            $table->boolean('shared_cost')->default(true)->after('actual_price');
            $table->text('notes_for_shopper')->nullable()->after('note');
        });

        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->timestamp('shopping_started_at')->nullable()->after('currently_shopping_by_id');
            $table->json('shopping_stats')->nullable()->after('actual_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_list_items', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_id']);
            $table->dropForeign(['bought_by_id']);
            $table->dropColumn([
                'claimed_by_id',
                'bought_by_id',
                'actual_price',
                'shared_cost',
                'notes_for_shopper',
            ]);
        });

        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->dropColumn(['shopping_started_at', 'shopping_stats']);
        });
    }
};
