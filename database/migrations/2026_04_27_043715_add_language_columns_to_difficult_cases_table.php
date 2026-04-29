<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('difficult_cases', function (Blueprint $table) {
            $table->string('source_lang', 10)->default('en');
            $table->string('target_lang', 10)->default('ru');
        });
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
