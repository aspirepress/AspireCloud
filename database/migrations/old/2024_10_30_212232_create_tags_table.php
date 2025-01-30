<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugin_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();    // more or less the user "slug", used in permalinks
            $table->string('name');
        });

        Schema::create('theme_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();    // more or less the user "slug", used in permalinks
            $table->string('name');
        });

        Schema::create('theme_theme_tags', function (Blueprint $table) {
            $table->foreignUuid('theme_id')->references('id')->on('themes');
            $table->foreignUuid('theme_tag_id')->references('id')->on('theme_tags');
            $table->primary(['theme_id', 'theme_tag_id']);  // Composite primary key
        });

        Schema::create('plugin_plugin_tags', function (Blueprint $table) {
            $table->foreignUuid('plugin_id')->references('id')->on('plugins');
            $table->foreignUuid('plugin_tag_id')->references('id')->on('plugin_tags');
            $table->primary(['plugin_id', 'plugin_tag_id']);  // Composite primary key
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_tags');
        Schema::dropIfExists('plugin_tags');
    }
};
