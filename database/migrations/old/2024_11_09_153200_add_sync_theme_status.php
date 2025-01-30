<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sync_themes', function (Blueprint $table) {
            $table->string('status')->after('updated')->default('open');
        });
    }

    public function down(): void
    {
        Schema::table('sync_themes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
