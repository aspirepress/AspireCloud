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
        Schema::table('themes', function (Blueprint $table) {
            $table->unique('slug','theme_slug_unique');
        });        Schema::table('plugins', function (Blueprint $table) {
            $table->unique('slug','plugin_slug_unique');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex('theme_slug_unique');
        });
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropIndex('plugin_slug_unique');
        });
    }
};
