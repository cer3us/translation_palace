<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations_memory', function (Blueprint $table) {
            $table->id();
            $table->text('source_text');
            $table->text('translated_text');
            $table->vector('embedding', 768)->nullable();   // still works because the pgvector package is partially loaded
            $table->boolean('is_gold')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        // Now create the HNSW index manually
        DB::statement('CREATE INDEX idx_memory_embedding ON translations_memory USING hnsw (embedding vector_cosine_ops)');

        // Partial index for approved (gold) memories
        DB::statement('CREATE INDEX idx_gold_memories ON translations_memory (created_at DESC) WHERE is_gold = true');

        // GIN index for JSON metadata
        DB::statement('CREATE INDEX idx_memory_metadata ON translations_memory USING gin (metadata)');

        // Optional: trigram index on source_text for fallback pattern matching (requires pg_trgm extension)
        // DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        // DB::statement('CREATE INDEX idx_source_trgm ON translations_memory USING gin (source_text gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('translations_memory');
    }
};