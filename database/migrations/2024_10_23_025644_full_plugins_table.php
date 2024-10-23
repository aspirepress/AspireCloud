<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sync_id')->nullable()->references('id')->on('sync_plugins');
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
            $table->string('business_model')->nullable(); // 'commercial'|'community'|false (we'll store false as a string)
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
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
