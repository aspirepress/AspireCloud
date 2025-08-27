<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('taggable_id');
            $table->string('taggable_type');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['taggable_type', 'taggable_id', 'slug'], 'taggable_slug_unique');
            $table->index(['taggable_type', 'taggable_id'], 'taggable_owner_idx');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
