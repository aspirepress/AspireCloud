<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::raw('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::raw('CREATE INDEX plugins_slug_trgm ON plugins USING GIST (slug gist_trgm_ops(siglen=48))');
        DB::raw('CREATE INDEX plugins_name_trgm ON plugins USING GIST (name gist_trgm_ops(siglen=48))');
        DB::raw(
            'CREATE INDEX plugins_short_description_trgm ON plugins USING GIST (short_description gist_trgm_ops(siglen=48))',
        );
    }

    public function down(): void
    {
        DB::raw('DROP INDEX plugins_short_description_trgm');
        DB::raw('DROP INDEX plugins_name_trgm');
        DB::raw('DROP INDEX plugins_slug_trgm');
    }
};
