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
            $table->string('short_description', 150);
            $table->text('description');
            $table->string('version')->index();
            $table->string('author')->index();
            $table->string('requires');
            $table->string('requires_php')->nullable();
            $table->string('tested');
            $table->string('download_link', 1024);
            $table->dateTime('added')->index();
            $table->dateTime('last_updated')->nullable()->index();
            $table->string('author_profile')->nullable();
            $table->unsignedSmallInteger('rating')->default(0)->index();
            $table->unsignedInteger('num_ratings')->default(0)->index();
            $table->unsignedInteger('support_threads')->default(0)->index();
            $table->unsignedInteger('support_threads_resolved')->default(0)->index();
            $table->unsignedInteger('active_installs')->default(0)->index();
            $table->unsignedInteger('downloaded')->default(0)->index();
            $table->string('homepage', 1024)->nullable();
            $table->string('donate_link', 1024)->nullable();
            $table->string('business_model')->nullable();
            $table->string('commercial_support_url', 1024)->nullable();
            $table->string('support_url', 1024)->nullable();
            $table->string('preview_link', 1024)->nullable();
            $table->string('repository_url', 1024)->nullable();
            $table->string('ac_origin')->nullable()->index();
            $table->dateTime('ac_created')->useCurrent()->index();
            $table->jsonb('ac_raw_metadata')->nullable();

            // additional indexes
            $table->string('slug')->fulltext()->change();
            $table->string('name')->fulltext()->change();
            $table->string('short_description')->fulltext()->change();
            $table->text('description')->fulltext()->change();
        });

        Schema::create('closed_plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->index();
            $table->string('name')->index();
            $table->text('description');
            $table->dateTime('closed_date')->index();
            $table->string('reason')->index();
            $table->foreignUuid('ac_shadow_id')->nullable()->references('id')->on('plugins')->nullOnDelete();
            $table->string('ac_origin')->nullable()->index();
            $table->dateTime('ac_created')->index();
            $table->jsonb('ac_raw_metadata')->nullable();

            // additional indexes
            $table->string('slug')->fulltext()->change();
            $table->string('name')->fulltext()->change();
            $table->string('description')->fulltext()->change();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_nicename')->index();
            $table->string('profile')->nullable();
            $table->string('avatar')->nullable();
            $table->string('display_name')->nullable()->index();
            $table->string('author')->nullable()->index();
            $table->string('author_url')->nullable();

            // additional indexes
            $table->string('user_nicename')->fulltext()->change();
            $table->string('display_name')->fulltext()->change();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->index();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('version')->index();
            $table->foreignUuid('author_id')->references('id')->on('authors');
            $table->string('download_link');
            $table->string('requires_php')->nullable();
            $table->datetime('last_updated')->index();
            $table->datetime('creation_time')->index();
            $table->string('preview_url')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->unsignedSmallInteger('rating')->default(0)->index();
            $table->unsignedInteger('num_ratings')->default(0)->index();
            $table->string('reviews_url')->nullable();
            $table->unsignedInteger('downloaded')->default(0)->index();
            $table->unsignedInteger('active_installs')->default(0)->index();
            $table->string('homepage')->nullable();
            $table->boolean('is_commercial')->default(false);
            $table->string('external_support_url')->nullable();
            $table->boolean('is_community')->default(false);
            $table->string('external_repository_url')->nullable();
            $table->string('ac_origin')->nullable()->index();
            $table->datetime('ac_created')->nullable()->index();
            $table->jsonb('ac_raw_metadata')->nullable();

            // additional indexes
            $table->string('slug')->fulltext()->change();
            $table->string('name')->fulltext()->change();
            $table->string('description')->fulltext()->change();
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
            $table->foreignUuid('theme_id')->references('id')->on('themes')->onDelete('cascade');
            $table->foreignUuid('theme_tag_id')->references('id')->on('theme_tags')->onDelete('cascade');
            $table->primary(['theme_id', 'theme_tag_id']);  // Composite primary key
        });

        Schema::create('plugin_plugin_tags', function (Blueprint $table) {
            $table->foreignUuid('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreignUuid('plugin_tag_id')->references('id')->on('plugin_tags')->onDelete('cascade');
            $table->primary(['plugin_id', 'plugin_tag_id']);  // Composite primary key
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_type');
            $table->string('slug');
            $table->string('version')->nullable();
            $table->string('revision')->nullable();
            $table->text('upstream_path');
            $table->text('local_path');
            $table->string('repository')->default('wp_org')->index();
            $table->timestamps();

            $table->index(['asset_type', 'slug']);
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
        Schema::dropIfExists('assets');
        Schema::dropIfExists('plugin_plugin_tags');
        Schema::dropIfExists('theme_theme_tags');
        Schema::dropIfExists('theme_tags');
        Schema::dropIfExists('plugin_tags');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('closed_plugins');
        Schema::dropIfExists('plugins');
    }
};
