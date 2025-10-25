<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_releases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('package_id')->constrained()->cascadeOnDelete();
            $table->text('version');
            $table->text('download_url')->nullable();
            // FAIR metadata (per release)
            $table->jsonb('requires')->nullable();
            $table->jsonb('suggests')->nullable();
            $table->jsonb('provides')->nullable();
            $table->jsonb('artifacts')->nullable();
            // Integrity info from the package artifact
            $table->text('signature')->nullable();
            $table->text('checksum')->nullable();

            $table->timestampTz('created_at')->useCurrent()->index();

            $table->unique(['package_id', 'version'], 'unique_pkg_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_releases');
    }
};
