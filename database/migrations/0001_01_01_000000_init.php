<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('migrations') && DB::table('migrations')->select('id')->exists()) {
            throw new RuntimeException('Existing migrations detected -- aborting');
        }
    }

    public function down(): void {}
};
