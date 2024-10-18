<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('current_version');
        });

        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plugin_id')
                ->references('id')
                ->on('plugins')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_url')->nullable();
            $table->string('type');
            $table->string('version');
            $table->index(['plugin_id', 'version', 'type']);
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
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('files');
        Schema::dropIfExists('plugins');
    }
};
