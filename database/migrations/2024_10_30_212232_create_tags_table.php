<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');    // more or less the user "slug", used in permalinks
            $table->foreignUuid('theme_id')->references('id')->on('themes');
            $table->unique(['slug', 'theme_id']);
        });


        Schema::create('plugin_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');    // more or less the user "slug", used in permalinks
            $table->foreignUuid('plugin_id')->references('id')->on('plugins');
            $table->unique(['slug', 'plugin_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('theme_tags');
        Schema::dropIfExists('plugin_tags');
    }
};
