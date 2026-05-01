<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('difficult_cases', function (Blueprint $table) {
            $table->vector('embedding', 768)->nullable();
        });

         // Now create the HNSW index manually
        DB::statement('CREATE INDEX idx_difficult_embedding ON difficult_cases USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('difficult_cases', function (Blueprint $table) {
            //
        });
    }
};
