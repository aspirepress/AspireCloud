<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->index();
            $table->string('name')->index();
            $table->string('short_description', 150)->fulltext();
            $table->text('description');
            $table->string('version');
            $table->string('author')->index();
            $table->string('requires');
            $table->string('requires_php');
            $table->string('tested');
            $table->string('download_link');
            $table->dateTime('added')->index();
            $table->dateTime('last_updated')->index();
            // all fields below are nullable or have a zero default
            $table->string('author_profile')->nullable();
            $table->unsignedSmallInteger('rating')->default(0);
            $table->jsonb('ratings')->nullable();
            $table->unsignedInteger('num_ratings')->default(0);
            $table->unsignedInteger('support_threads')->default(0);
            $table->unsignedInteger('support_threads_resolved')->default(0);
            $table->unsignedInteger('active_installs')->default(0);
            $table->unsignedInteger('downloaded')->default(0);
            $table->string('homepage')->nullable();
            $table->jsonb('banners')->nullable();
            $table->jsonb('tags')->nullable();   // denormalized, will be in a join table too
            $table->string('donate_link')->nullable();
            $table->jsonb('contributors')->nullable();
            $table->jsonb('icons')->nullable();
            $table->jsonb('source')->nullable();
            $table->string('business_model')->nullable(
            ); // 'commercial'|'community'|false (we'll store false as a string)
            $table->string('commercial_support_url')->nullable();
            $table->string('support_url')->nullable();
            $table->string('preview_link')->nullable();
            $table->string('repository_url')->nullable();
            $table->jsonb('requires_plugins')->nullable();   // string[]
            $table->jsonb('compatibility')->nullable();      // string[]
            $table->jsonb('screenshots')->nullable();
            $table->jsonb('sections')->nullable();
            $table->jsonb('versions')->nullable();
            $table->jsonb('upgrade_notice')->nullable();
        });

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

        Schema::create('request_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_path');
            $table->json('request_query_params')->nullable();
            $table->json('request_body')->nullable();
            $table->json('request_headers');
            $table->integer('response_code');
            $table->text('response_body')->nullable();
            $table->json('response_headers');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_data');
        Schema::dropIfExists('plugin_plugin_tags');
        Schema::dropIfExists('theme_theme_tags');
        Schema::dropIfExists('theme_tags');
        Schema::dropIfExists('plugin_tags');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('plugins');
    }
};
