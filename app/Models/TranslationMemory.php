<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class TranslationMemory extends Model
{
    use HasNeighbors;

    protected $table = 'translations_memory';

    protected $fillable = [
        'source_text',
        'translated_text',
        'embedding',
        'is_gold',
        'metadata',
        'target_lang',
        'source_lang',
    ];

    protected $casts = [
        'embedding' => Vector::class,
        'is_gold'   => 'boolean',
        'metadata'  => 'array',        // jsonb → PHP array
    ];
}