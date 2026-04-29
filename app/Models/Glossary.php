<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
    protected $table = 'glossary';

    protected $fillable = [
        'term',
        'translation',
        'context_tag',
        'context_priority',
        'metadata',
        'target_lang',
        'source_lang',
    ];

    protected $casts = [
        'context_priority' => 'array',   // jsonb
        'metadata'         => 'array',
    ];

    /**
     * Fix translation for a given term, considering optional context.
     */
    public static function translate(string $term, ?string $context = null): ?string
    {
        $entry = static::where('term', $term)->first();
        if (!$entry) return null;

        // If a context is provided and the entry has a priority override, use it
        if ($context && isset($entry->context_priority[$context])) {
            return $entry->context_priority[$context];
        }

        // Fallback to default translation
        return $entry->translation;
    }
}