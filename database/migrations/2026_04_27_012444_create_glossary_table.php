<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossary', function (Blueprint $table) {
            $table->id();
            $table->string('term')->unique();
            $table->string('translation');
            $table->string('context_tag')->nullable();   // simple domain tag (e.g., 'laravel', 'gaming')
            $table->jsonb('context_priority')->nullable(); // detailed overrides, e.g. {"laravel": "Контроллер", "default": "Диспетчер"}
            $table->jsonb('metadata')->nullable();        // any additional notes
            $table->timestamps();
        });

        // Index on context_tag for domain-scoped lookups
        DB::statement('CREATE INDEX idx_glossary_context_tag ON glossary (context_tag)');

        // GIN index on context_priority for quick checks (e.g., ?| array keys)
        DB::statement('CREATE INDEX idx_glossary_priority ON glossary USING gin (context_priority)');
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary');
    }
};