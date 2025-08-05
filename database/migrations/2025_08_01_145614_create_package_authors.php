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
        Schema::create('package_authors', function (Blueprint $table) {
            $table->foreignUuid('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreignUuid('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->primary(['package_id', 'author_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_authors');
    }
};
