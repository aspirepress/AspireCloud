<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('author_package', function (Blueprint $table) {
            $table->foreignUuid('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreignUuid('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->primary(['package_id', 'author_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_package');
    }
};
