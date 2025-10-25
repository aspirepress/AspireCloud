<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->text('key')->primary();
            $table->text('value');
            $table->bigInteger('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->text('key')->primary();
            $table->text('owner');
            $table->bigInteger('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->text('queue')->index();
            $table->text('payload');
            $table->integer('attempts');
            $table->bigInteger('reserved_at')->nullable();
            $table->bigInteger('available_at');
            $table->bigInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->text('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids');
            $table->text('options')->nullable();
            $table->bigInteger('cancelled_at')->nullable();
            $table->bigInteger('created_at');
            $table->bigInteger('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->text('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->text('payload');
            $table->text('exception');
            $table->timestampTz('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
