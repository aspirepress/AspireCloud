<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('email')->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->text('password');
            $table->text('remember_token')->nullable();

            // Fortify
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestampTz('two_factor_confirmed_at')->nullable();

            // Jetstream cruft, apparently required according to the migration name
            $table->foreignId('current_team_id')->nullable(); // not actually a fk to anything
            $table->text('profile_photo_path')->nullable();

            // Last things last
            $table->timestampTz('created_at')->useCurrent()->index();
            $table->timestampTz('updated_at')->nullable()->index();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->text('email')->primary();
            $table->text('token');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->text('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->text('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->bigInteger('last_activity')->index();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->text('token')->unique();
            $table->text('abilities')->nullable();
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
