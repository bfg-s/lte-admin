<?php

return [
    /**
     * Global rout configurations
     */
    'route' => [
        'namespace' => 'App\LteAdmin\Controllers',
        'prefix' => 'lte',
        'name' => 'lte.',
        'layout' => 'lte_layout'
    ],

    /**
     * Package work dirs
     */
    'paths' => [
        'app' => app_path('LteAdmin'),
        'view' => 'admin'
    ],

    /**
     * Authentication settings for all lar admin pages. Include an authentication
     * guard and a user provider setting of authentication driver.
     */
    'auth' => [

        'guards' => [
            'lte' => [
                'driver'   => 'session',
                'provider' => 'lte',
            ],
        ],

        'providers' => [
            'lte' => [
                'driver' => 'eloquent',
                'model'  => \Lar\LteAdmin\Models\LteUser::class,
            ],
        ],
    ],

    /**
     * Admin lte upload setting
     *
     * File system configuration for form upload files and images, including
     * disk and upload path.
     */
    'upload' => [

        'disk' => 'lte',

        /**
         * Image and file upload path under the disk above.
         */
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],
    ],

    'gets' => [
        'lte' => [
            'menu' => \Lar\LteAdmin\Getters\Menu::class
        ]
    ],

    /**
     * Admin lte use disks
     */
    'disks' => [
        'lte' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'visibility' => 'public',
            'url' => env('APP_URL').'/uploads',
        ]
    ],

    'footer' => [
        'copy' => '<strong>Copyright &copy; '.date('Y').'.</strong> All rights reserved.'
    ],

    'lang_flags' => [
        'uk' => 'flag-icon flag-icon-ua',
        'en' => 'flag-icon flag-icon-us',
        'ru' => 'flag-icon flag-icon-ru',
    ]
];