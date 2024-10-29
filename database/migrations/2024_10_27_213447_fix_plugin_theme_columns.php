<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plugins', static function (Blueprint $table) {
            $table->string('requires_php')->nullable()->change();
            $table->string('last_updated')->nullable()->change();
            $table->string('download_link', 1024)->change();
            $table->string('donate_link', 1024)->nullable()->change();
            $table->string('homepage', 1024)->nullable()->change();
            $table->string('commercial_support_url', 1024)->nullable()->change();
            $table->string('support_url', 1024)->nullable()->change();
            $table->string('preview_link', 1024)->nullable()->change();
            $table->string('repository_url', 1024)->nullable()->change();
        });
        Schema::table('themes', static function (Blueprint $table) {
            $table->string('requires_php')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plugins', static function (Blueprint $table) {
            $table->string('requires_php')->change();
            $table->string('last_updated')->change();
            $table->string('download_link', 255)->change();
            $table->string('donate_link', 255)->nullable()->change();
            $table->string('homepage', 255)->nullable()->change();
            $table->string('commercial_support_url', 255)->nullable()->change();
            $table->string('support_url', 255)->nullable()->change();
            $table->string('preview_link', 255)->nullable()->change();
            $table->string('repository_url', 255)->nullable()->change();
        });
        Schema::table('themes', static function (Blueprint $table) {
            $table->string('requires_php')->change();
        });
    }
};
