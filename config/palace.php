<?php

return [
    'domains' => [
        'tech'     => 'Tech',
        'law'      => 'Law',
        'business' => 'Business',
        'science'  => 'Science',
        'gaming'   => 'Gaming',
        'medical'  => 'Medical',
        'everyday' => 'Everyday',
        'philosophy' => 'Philosophy',
    ],

    'wings' => [
        'tech' => [
            'laravel' => 'Laravel',
            'vue'     => 'Vue.js',
            'docker'  => 'Docker',
            'linux'   => 'Linux',
            'nginx'   => 'Nginx',
            'misc'   => 'Miscellaneous',
        ],
        'gaming' => [
            'minecraft' => 'Minecraft',
            'csgo'      => 'CS:GO',
        ],
        'law' => [
            'misc'   => 'Miscellaneous',
        ],
        'science' => [
            'misc'   => 'Miscellaneous',
        ],
        // ... add as needed
    ],

    'rooms' => [
        'laravel' => [
            'routing'    => 'Routing',
            'containers' => 'Containers',
            'eloquent'   => 'Eloquent',
        ],
        'docker' => [
            'compose'  => 'Docker Compose',
            'networks' => 'Networks',
        ],
        // ... etc.
    ],

    'tags' => [
        'slang',
        'technical',
        'formal',
        'informal',
        'idiom',
        'error',
    ],
];