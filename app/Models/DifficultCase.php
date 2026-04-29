<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DifficultCase extends Model
{
    protected $table = 'difficult_cases';

    protected $fillable = [
        'source_phrase',
        'target_translation',
        'explanation',
        'tags',
        'target_lang',
        'source_lang',
        'metadata'
    ];

    protected $casts = [
        'tags' => 'array',    // jsonb → PHP array
    ];
}