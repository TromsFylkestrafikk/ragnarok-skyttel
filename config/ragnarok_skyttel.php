<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Remote file system disk for Consat
     |--------------------------------------------------------------------------
     |
     | This uses the same options as given in laravel's config/filesystems.php
     |
     */
    'remote_disk' => [
        'driver' => 'sftp',
        'host' => env('SKYTTEL_HOST'),
        'username' => env('SKYTTEL_USER'),
        'password' => env('SKYTTEL_PASS'),
        'port' => (int) env('SKYTTEL_PORT', 22),
        'root' => env('SKYTTEL_ROOT', '/'),
        'timeout' => 30,
        'visibility' => 'public',
        'directory_visibility' => 'public',
    ],
];
