<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('package_release_id')->constrained('package_releases')->cascadeOnDelete();

            // Type of vulnerability data (currently only security)
            $table->string('type')->default('security');

            $table->string('class')->default(null)->nullable();

            // Vulnerability status: low, medium, high
            $table->string('value')->index();

            // Complete vulnerability data from API
            $table->jsonb('data');

            // Source of the vulnerability information
            $table->text('source');

            // Timestamps
            $table->timestampTz('created_at')->useCurrent()->index();
            $table->timestampTz('updated_at')->useCurrent();

            // Index for efficient queries
            $table->index(['package_release_id', 'value']);
            $table->index(['value', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
