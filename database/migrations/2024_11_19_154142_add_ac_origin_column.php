<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plugins', fn(Blueprint $table) => $table->string('ac_origin')->nullable());
        Schema::table('closed_plugins', fn(Blueprint $table) => $table->string('ac_origin')->nullable());
        Schema::table('themes', fn(Blueprint $table) => $table->string('ac_origin')->nullable());
    }

    public function down(): void
    {
        Schema::table('plugins', fn(Blueprint $table) => $table->dropColumn('ac_origin'));
        Schema::table('closed_plugins', fn(Blueprint $table) => $table->dropColumn('ac_origin'));
        Schema::table('themes', fn(Blueprint $table) => $table->dropColumn('ac_origin'));
    }
};
