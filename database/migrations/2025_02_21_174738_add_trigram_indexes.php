<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::raw('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::raw('CREATE INDEX plugins_slug_trgm ON plugins USING GIST (slug gist_trgm_ops(siglen=32))');
        DB::raw('CREATE INDEX plugins_name_trgm ON plugins USING GIST (name gist_trgm_ops(siglen=32))');
        DB::raw(
            'CREATE INDEX plugins_short_description_trgm ON plugins USING GIST (short_description gist_trgm_ops(siglen=32))',
        );
        DB::raw('CREATE INDEX plugins_author_trgm ON plugins USING GIST (author gist_trgm_ops(siglen=32))');

        DB::raw('CREATE INDEX themes_slug_trgm ON themes USING GIST (slug gist_trgm_ops(siglen=32))');
        DB::raw('CREATE INDEX themes_name_trgm ON themes USING GIST (name gist_trgm_ops(siglen=32))');

        DB::raw(
            'CREATE INDEX author_user_nicename_trgm on authors using GIST (user_nicename gist_trgm_ops(siglen=32))',
        );
        DB::raw('CREATE INDEX author_display_name_trgm on authors using GIST (display_name gist_trgm_ops(siglen=32))');
    }

    public function down(): void
    {
        DB::raw('DROP INDEX IF EXISTS author_display_name_trgm');
        DB::raw('DROP INDEX IF EXISTS author_user_nicename_trgm');
        DB::raw('DROP INDEX IF EXISTS themes_name_trgm');
        DB::raw('DROP INDEX IF EXISTS themes_slug_trgm');
        DB::raw('DROP INDEX IF EXISTS plugins_author_trgm');
        DB::raw('DROP INDEX IF EXISTS plugins_short_description_trgm');
        DB::raw('DROP INDEX IF EXISTS plugins_name_trgm');
        DB::raw('DROP INDEX IF EXISTS plugins_slug_trgm');
    }
};
