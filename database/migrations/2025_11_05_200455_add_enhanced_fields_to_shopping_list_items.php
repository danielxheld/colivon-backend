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
            $table->string('category')->nullable()->after('unit');
            $table->text('note')->nullable()->after('category');
            $table->decimal('price', 10, 2)->nullable()->after('note');
            $table->integer('aisle_order')->nullable()->after('price');
            $table->string('image_url')->nullable()->after('aisle_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_list_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'note', 'price', 'aisle_order', 'image_url']);
        });
    }
};
