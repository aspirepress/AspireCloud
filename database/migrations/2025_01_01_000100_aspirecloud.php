<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('slug')->index();
            $table->text('name')->index();
            $table->text('short_description');
            $table->text('description');
            $table->text('version')->index();
            $table->text('author')->index();
            $table->text('requires')->nullable();
            $table->text('requires_php')->nullable();
            $table->text('tested')->nullable();
            $table->text('download_link');
            $table->timestampTz('added')->nullable()->index();
            $table->timestampTz('last_updated')->nullable()->index();
            $table->text('author_profile')->nullable();
            $table->smallInteger('rating')->default(0)->index();
            $table->integer('num_ratings')->default(0)->index();
            $table->integer('support_threads')->default(0)->index();
            $table->integer('support_threads_resolved')->default(0)->index();
            $table->integer('active_installs')->default(0)->index();
            $table->integer('downloaded')->default(0)->index();
            $table->text('homepage')->nullable();
            $table->text('donate_link')->nullable();
            $table->text('business_model')->nullable();
            $table->text('commercial_support_url')->nullable();
            $table->text('support_url')->nullable();
            $table->text('preview_link')->nullable();
            $table->text('repository_url')->nullable();
            $table->text('ac_origin')->index();
            $table->timestampTz('ac_created')->useCurrent()->index();
            $table->jsonb('ac_raw_metadata');

            // additional indexes
            $table->text('slug')->fulltext()->change();
            $table->text('name')->fulltext()->change();
            $table->text('short_description')->fulltext()->change();
            $table->text('description')->fulltext()->change();
        });

        Schema::create('closed_plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('slug')->index();
            $table->text('name')->index();
            $table->text('description');
            $table->timestampTz('closed_date')->index();
            $table->text('reason')->index();
            $table->foreignUuid('ac_shadow_id')->nullable()->references('id')->on('plugins')->nullOnDelete();
            $table->text('ac_origin')->nullable()->index();
            $table->timestampTz('ac_created')->index();
            $table->jsonb('ac_raw_metadata')->nullable();

            // additional indexes
            $table->text('slug')->fulltext()->change();
            $table->text('name')->fulltext()->change();
            $table->text('description')->fulltext()->change();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('user_nicename')->index();
            $table->text('profile')->nullable();
            $table->text('avatar')->nullable();
            $table->text('display_name')->nullable()->index();
            $table->text('author')->nullable()->index();
            $table->text('author_url')->nullable();

            // additional indexes
            $table->text('user_nicename')->fulltext()->change();
            $table->text('display_name')->fulltext()->change();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('slug')->index();
            $table->text('name')->index();
            $table->text('version')->index();
            $table->text('description');
            $table->foreignUuid('author_id')->references('id')->on('authors');
            $table->text('download_link');
            $table->text('requires')->nullable();
            $table->text('requires_php')->nullable();
            $table->timestampTz('last_updated')->nullable()->index();
            $table->timestampTz('creation_time')->nullable()->index();
            $table->text('preview_url')->nullable();
            $table->text('screenshot_url')->nullable();
            $table->smallInteger('rating')->default(0)->index();
            $table->integer('num_ratings')->default(0)->index();
            $table->text('reviews_url')->nullable();
            $table->integer('downloaded')->default(0)->index();
            $table->integer('active_installs')->default(0)->index();
            $table->text('homepage')->nullable();
            $table->boolean('is_commercial')->default(false);
            $table->text('external_support_url')->nullable();
            $table->boolean('is_community')->default(false);
            $table->text('external_repository_url')->nullable();
            $table->text('ac_origin')->index();
            $table->timestampTz('ac_created')->useCurrent()->index();
            $table->jsonb('ac_raw_metadata');

            // additional indexes
            $table->text('slug')->fulltext()->change();
            $table->text('name')->fulltext()->change();
            $table->text('description')->fulltext()->change();
        });

        Schema::create('plugin_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('slug')->unique();    // more or less the user "slug", used in permalinks
            $table->text('name');
        });

        Schema::create('theme_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('slug')->unique();    // more or less the user "slug", used in permalinks
            $table->text('name');
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
            $table->text('asset_type');
            $table->text('slug');
            $table->text('version')->nullable();
            $table->text('revision')->nullable();
            $table->text('upstream_path');
            $table->text('local_path');
            $table->text('repository')->default('wp_org')->index();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index(['asset_type', 'slug']);
        });

        Schema::create('request_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('request_path');
            $table->json('request_query_params')->nullable();
            $table->json('request_body')->nullable();
            $table->json('request_headers');
            $table->integer('response_code');
            $table->text('response_body')->nullable();
            $table->json('response_headers');
            $table->timestampTz('created_at')->useCurrent();
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
