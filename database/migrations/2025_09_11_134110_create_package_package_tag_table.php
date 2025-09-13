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
        Schema::create('package_package_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('package_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('package_tag_id')->constrained()->cascadeOnDelete();

            $table->unique(['package_id', 'package_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_package_tag');
    }
};
