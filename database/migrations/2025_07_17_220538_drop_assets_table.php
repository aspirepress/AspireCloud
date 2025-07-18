<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('assets');
    }

    public function down(): void
    {
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
    }
};
