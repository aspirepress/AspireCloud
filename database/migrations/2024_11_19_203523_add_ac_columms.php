<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dateTime('ac_created')->useCurrent();
            $table->jsonb('ac_raw_metadata')->nullable();
        });
        Schema::table('themes', function (Blueprint $table) {
            $table->dateTime('ac_created')->useCurrent();
            $table->jsonb('ac_raw_metadata')->nullable();
        });
        Schema::table('closed_plugins', function (Blueprint $table) {
            $table->renameColumn('metadata', 'ac_raw_metadata');
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn('ac_created');
            $table->dropColumn('ac_raw_metadata');
        });
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn('ac_created');
            $table->dropColumn('ac_raw_metadata');
        });
        Schema::table('closed_plugins', function (Blueprint $table) {
            $table->renameColumn('ac_raw_metadata', 'metadata');
        });
    }
};
