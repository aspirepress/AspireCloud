<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // We list every reasonable index, ones added in previous migrations are commented out.
        // less reasonable ones are marked with '//??' and should be first to be deleted if necessary

        Schema::table('plugins', function (Blueprint $table) {
            // $table->string('slug')->index();
            $table->string('slug')->fulltext()->change();
            // $table->string('name')->index();
            $table->string('name')->fulltext()->change();
            // $table->string('short_description')->fulltext()->change();
            $table->text('description')->fulltext()->change();
            $table->string('version')->index()->change(); //??
            // $table->string('author')->index();
            // $table->dateTime('added')->index();
            // $table->dateTime('last_updated')->index();
            $table->unsignedSmallInteger('rating')->index()->change();
            $table->unsignedInteger('num_ratings')->index()->change(); //??
            $table->unsignedInteger('support_threads')->index()->change(); //??
            $table->unsignedInteger('support_threads_resolved')->index()->change(); //??
            $table->unsignedInteger('active_installs')->index()->change();
            $table->unsignedInteger('downloaded')->index()->change();
            $table->string('ac_origin')->index()->change();
            $table->string('ac_created')->index()->change();
        });

        Schema::table('themes', function (Blueprint $table) {
            // Already indexed: id description(ft-only)
            $table->string('slug')->index()->change();
            $table->string('slug')->fulltext()->change();
            $table->string('name')->index()->change();
            $table->string('name')->fulltext()->change();
            $table->string('version')->index()->change();
            $table->dateTime('last_updated')->index()->change();
            $table->dateTime('creation_time')->index()->change();
            $table->unsignedSmallInteger('rating')->index()->change();
            $table->unsignedInteger('num_ratings')->index()->change();
            $table->unsignedInteger('downloaded')->index()->change();
            $table->unsignedInteger('active_installs')->index()->change();
            // $table->text('description')->fulltext()->change();
            $table->string('ac_origin')->index()->change();
            $table->dateTime('ac_created')->index()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropFullText('plugins_slug_fulltext');
            $table->dropFullText('plugins_name_fulltext');
            $table->dropFullText('plugins_description_fulltext');
            $table->dropIndex('plugins_ac_created_index');
            $table->dropIndex('plugins_ac_origin_index');
            $table->dropIndex('plugins_active_installs_index');
            $table->dropIndex('plugins_downloaded_index');
            $table->dropIndex('plugins_num_ratings_index');
            $table->dropIndex('plugins_rating_index');
            $table->dropIndex('plugins_support_threads_index');
            $table->dropIndex('plugins_support_threads_resolved_index');
            $table->dropIndex('plugins_version_index');
        });
        Schema::table('themes', function (Blueprint $table) {
            $table->dropFullText('themes_slug_fulltext');
            $table->dropFullText('themes_name_fulltext');
            // $table->dropFullText('themes_description_fulltext');
            $table->dropIndex('themes_slug_index');
            $table->dropIndex('themes_name_index');
            $table->dropIndex('themes_ac_created_index');
            $table->dropIndex('themes_ac_origin_index');
            $table->dropIndex('themes_active_installs_index');
            $table->dropIndex('themes_downloaded_index');
            $table->dropIndex('themes_rating_index');
            $table->dropIndex('themes_num_ratings_index');
            $table->dropIndex('themes_version_index');
            $table->dropIndex('themes_last_updated_index');
            $table->dropIndex('themes_creation_time_index');
        });
    }
};
