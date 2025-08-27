<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_releases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('package_id')->constrained()->cascadeOnDelete();
            $table->text('version')->default('0.0.0');
            $table->text('download_url')->nullable();
            // FAIR metadata (per release)
            $table->json('requires')->nullable();
            $table->json('suggests')->nullable();
            $table->json('provides')->nullable();
            $table->json('artifacts')->nullable();
            // Integrity info from the package artifact
            $table->string('signature', 512)->nullable();
            $table->string('checksum', 200)->nullable();

            $table->timestamps();

            $table->unique(['package_id', 'version'], 'unique_pkg_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_releases');
    }
};
