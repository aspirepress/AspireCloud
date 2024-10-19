<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('current_version');
            $table->dateTime('updated')->useCurrent();
            $table->string('status')->default('open');
            $table->dateTime('pulled_at')->useCurrent();
            $table->jsonb('metadata')->nullable();
        });

        Schema::create('plugin_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plugin_id')
                ->references('id')
                ->on('plugins')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('file_url')->nullable();
            $table->string('type');
            $table->string('version');
            $table->jsonb('metadata')->nullable();
            $table->dateTime('created')->useCurrent();
            $table->dateTime('processed')->nullable();
            $table->string('hash')->nullable();
            $table->unique(['plugin_id', 'version', 'type']);
            $table->index('hash');
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('host')->unique();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->foreignUuid('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('key_prefix');
            $table->dateTime('revoked')->nullable();
            $table->index(['site_id', 'key_prefix']);
        });

        Schema::create('revisions', function (Blueprint $table) {
            $table->string('action');
            $table->string('revision');
            $table->dateTime('added_at')->useCurrent();
            $table->index('action');
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('current_version', 255);
            $table->dateTime('updated')->useCurrent();
            $table->dateTime('pulled_at')->useCurrent();
            $table->jsonb('metadata')->nullable();
        });

        Schema::create('theme_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('theme_id')
                ->references('id')
                ->on('themes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('file_url')->nullable();
            $table->string('type');
            $table->string('version');
            $table->jsonb('metadata')->nullable();
            $table->dateTime('created')->useCurrent();
            $table->dateTime('processed')->nullable();
            $table->string('hash')->nullable();
            $table->index('hash');
            $table->unique(['theme_id', 'version', 'type']);
        });

        Schema::create('not_found_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_type');
            $table->string('item_slug');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('command', 255);
            $table->jsonb('stats');
            $table->dateTime('created_at')->useCurrent();
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
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('files');
        Schema::dropIfExists('plugins');
    }
};
