<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->string('slug')->fulltext()->change();
            $table->string('name')->fulltext()->change();
        });
        Schema::table('themes', function (Blueprint $table) {
            $table->string('slug')->fulltext()->change();
            $table->string('name')->fulltext()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropFullText('plugins_slug_fulltext');
            $table->dropFullText('plugins_name_fulltext');
        });
        Schema::table('themes', function (Blueprint $table) {
            $table->dropFullText('themes_slug_fulltext');
            $table->dropFullText('themes_name_fulltext');
        });
    }
};
