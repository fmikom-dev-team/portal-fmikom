<?php

use Illuminate\Hashing\BcryptHasher;
use Illuminate\Hashing\ArgonHasher;
use Illuminate\Hashing\Argon2IdHasher;

return [
    'driver' => env('HASH_DRIVER', 'bcrypt'),

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => false,
        'limit' => null,
    ],

    'argon' => [
        'memory' => env('HASH_MEMORY', 65536),
        'threads' => env('HASH_THREADS', 1),
        'time' => env('HASH_TIME', 4),
        'verify' => false,
    ],

    'argon2id' => [
        'memory' => env('HASH_MEMORY', 65536),
        'threads' => env('HASH_THREADS', 1),
        'time' => env('HASH_TIME', 4),
        'verify' => false,
    ],
];
