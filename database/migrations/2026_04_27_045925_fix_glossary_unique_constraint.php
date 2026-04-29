<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old single-column unique index
        DB::statement('ALTER TABLE glossary DROP CONSTRAINT IF EXISTS glossary_term_unique');
        
        // Create the new composite unique index
        DB::statement('CREATE UNIQUE INDEX glossary_term_lang_unique ON glossary (term, source_lang, target_lang)');
    }

    public function down(): void
    {
        // Reverse: drop composite, recreate single-column
        DB::statement('DROP INDEX IF EXISTS glossary_term_lang_unique');
        DB::statement('ALTER TABLE glossary ADD CONSTRAINT glossary_term_unique UNIQUE (term)');
    }
};
