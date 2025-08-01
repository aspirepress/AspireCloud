<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('package_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name');
            $table->text('slug');
            $table->text('description')->nullable();
            $table->foreignUuid('origin_id')->constrained()->cascadeOnDelete();
            $table->unique(['slug', 'origin_id'], 'unique_slug_per_origin');

            $table->timestampTz('created_at')->useCurrent()->index();
            $table->timestampTz('updated_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_types');
    }
};
