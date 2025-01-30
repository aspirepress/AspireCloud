<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('theme_theme_tags', function (Blueprint $table) {
            $table->dropForeign('theme_theme_tags_theme_id_foreign');
            $table->dropForeign('theme_theme_tags_theme_tag_id_foreign');
            $table->foreign('theme_id')->references('id')->on('themes')->onDelete('cascade');
            $table->foreign('theme_tag_id')->references('id')->on('theme_tags')->onDelete('cascade');
        });

        Schema::table('plugin_plugin_tags', function (Blueprint $table) {
            $table->dropForeign('plugin_plugin_tags_plugin_id_foreign');
            $table->dropForeign('plugin_plugin_tags_plugin_tag_id_foreign');
            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreign('plugin_tag_id')->references('id')->on('plugin_tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('theme_theme_tags', function (Blueprint $table) {
            $table->dropForeign('theme_theme_tags_theme_id_foreign');
            $table->dropForeign('theme_theme_tags_theme_tag_id_foreign');
            $table->foreign('theme_id')->references('id')->on('themes')->onDelete('restrict');
            $table->foreign('theme_tag_id')->references('id')->on('theme_tags')->onDelete('restrict');
        });

        Schema::table('plugin_plugin_tags', function (Blueprint $table) {
            $table->dropForeign('plugin_plugin_tags_plugin_id_foreign');
            $table->dropForeign('plugin_plugin_tags_plugin_tag_id_foreign');
            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('restrict');
            $table->foreign('plugin_tag_id')->references('id')->on('plugin_tags')->onDelete('restrict');
        });
    }
};
