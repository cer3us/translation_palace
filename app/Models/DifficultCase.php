<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;


class DifficultCase extends Model
{
    use HasNeighbors;

    protected $table = 'difficult_cases';

    protected $fillable = [
        'source_phrase',
        'target_translation',
        'explanation',
        'tags',
        'target_lang',
        'source_lang',
        'metadata',
        'embedding'
    ];

    protected $casts = [
        'embedding' => Vector::class,
        'tags' => 'array',    // jsonb → PHP array'
        'metadata' => 'array',
    ];
}