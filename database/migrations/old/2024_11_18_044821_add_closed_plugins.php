<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('closed_plugins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
            $table->string('name');
            $table->text('description');
            $table->dateTime('closed_date');
            $table->string('reason');
            $table->jsonb('metadata')->nullable();
            $table->dateTime('ac_created');
            $table->foreignUuid('ac_shadow_id')->nullable()->references('id')->on('plugins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closed_plugins');
    }
};
