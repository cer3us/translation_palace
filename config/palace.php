<?php

return [
    'domains' => [
        'tech'     => 'Tech',
        'gaming'   => 'Gaming',
        'medical'  => 'Medical',
        'everyday' => 'Everyday',
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