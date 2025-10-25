<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->timestampTz('created_at')->useCurrent()->index();

            $table->unique(['slug'], 'slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tags');
    }
};
