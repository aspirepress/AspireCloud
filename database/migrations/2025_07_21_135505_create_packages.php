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
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Identity
            $table->string('did')->nullable();
            $table->text('slug');
            $table->text('name');
            $table->text('description')->nullable();
            // Origin & type
            $table->foreignUuid('origin_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('package_type_id')->constrained()->cascadeOnDelete();
            // Timestamps
            $table->timestampTz('created_at')->useCurrent()->index();
            $table->timestampTz('updated_at')->nullable()->index();
            // Constraints
            $table->unique(['did'], 'unique_package_did');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
