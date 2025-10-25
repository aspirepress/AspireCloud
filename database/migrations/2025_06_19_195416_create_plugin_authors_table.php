<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugin_authors', function (Blueprint $table) {
            $table->foreignUuid('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->foreignUuid('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->primary(['plugin_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_authors');
    }
};
