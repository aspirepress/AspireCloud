<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('package_id')->constrained()->cascadeOnDelete();
            $table->text('version')->default('1.0.0');
            $table->text('download_url');
            $table->json('raw_metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_releases');
    }
};
