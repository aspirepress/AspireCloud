<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_nicename');    // more or less the user "slug", used in permalinks
            $table->string('profile')->nullable();
            $table->string('avatar')->nullable();
            $table->string('display_name')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sync_id')->nullable()->references('id')->on('sync_themes');
            $table->string('slug');
            $table->string('name');
            $table->string('version');
            $table->foreignUuid('author_id')->references('id')->on('authors');
            $table->string('download_link');
            $table->string('requires_php');
            $table->datetime('last_updated');
            $table->datetime('creation_time');
            $table->string('preview_url')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->jsonb('ratings')->nullable();
            $table->unsignedSmallInteger('rating')->default(0);
            $table->unsignedInteger('num_ratings')->default(0);
            $table->string('reviews_url')->nullable();
            $table->unsignedInteger('downloaded')->default(0);
            $table->unsignedInteger('active_installs')->default(0);
            $table->string('homepage')->nullable();
            $table->jsonb('sections')->nullable();
            $table->jsonb('tags')->nullable();
            $table->jsonb('versions')->nullable();
            $table->jsonb('requires')->nullable();
            $table->boolean('is_commercial')->default(false);
            $table->string('external_support_url')->nullable();
            $table->boolean('is_community')->default(false);
            $table->string('external_repository_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
        Schema::dropIfExists('authors');
    }
};
