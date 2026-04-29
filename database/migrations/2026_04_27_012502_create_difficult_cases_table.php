<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('difficult_cases', function (Blueprint $table) {
            $table->id();
            $table->text('source_phrase');
            $table->text('target_translation');
            $table->text('explanation')->nullable();
            $table->jsonb('tags')->nullable();            // context tags, e.g. ["slang", "formal"]
            $table->timestamps();
        });

        // GIN index on tags for array containment queries (WHERE tags @> '["slang"]')
        DB::statement('CREATE INDEX idx_difficult_tags ON difficult_cases USING gin (tags)');

        // Optional: trigram index on source_phrase for fuzzy matching (requires pg_trgm extension)
        // DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        // DB::statement('CREATE INDEX idx_difficult_phrase_trgm ON difficult_cases USING gin (source_phrase gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('difficult_cases');
    }
};