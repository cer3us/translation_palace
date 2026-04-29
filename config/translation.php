<?php

return [
    'languages' => [
        'en' => 'English',
        'ru' => 'Russian',
        'cn' => 'Chinese',
    ],

    'default_source' => 'en',
    'default_target' => 'ru',
    
    'vector_threshold' => env('VECTOR_THRESHOLD', 0.4),

    'model' => env('TRANSLATION_MODEL', 'qwen2.5-coder:3b'),
    'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
    'embedding_dimension' => env('OLLAMA_EMBEDDING_DIMENSION', 768),
    'embedding_host' => env('OLLAMA_EMBEDDING_HOST', 'http://172.27.240.1:11434'),

    'provider' => env('TRANSLATION_PROVIDER', 'ollama'),
];