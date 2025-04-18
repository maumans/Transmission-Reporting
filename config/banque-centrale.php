<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des APIs de la Banque Centrale
    |--------------------------------------------------------------------------
    */

    'base_url' => env('BANQUE_CENTRALE_API_URL', 'https://syrif.bcrg-guinee.org:8186'),
    
    'endpoints' => [
        // Authentification
        'auth' => [
            'signin' => '/auth/signin',
            'refresh' => '/auth/refresh',
            'logout' => '/auth/logout',
        ],
        
        // Gestion des comptes
        'util' => [
            'reset_password' => '/util/rpwd',
            'forgot_password' => '/util/fpwd',
        ],

        // Gestion des fichiers SITU
        'situ' => [
            'import' => [
                'situ01' => '/api/situ/situ01',
                'situ02' => '/api/situ/situ02',
                'situ03' => '/api/situ/situ03',
            ],
            'calcul' => [
                'situ01' => '/api/calcul/situ/situ01',
                'situ02' => '/api/calcul/situ/situ02',
                'situ03' => '/api/calcul/situ/situ03',
            ],
            'rapport' => '/etats/situ/rapport',
        ],
    ],

    'timeout' => env('BANQUE_CENTRALE_TIMEOUT', 30),
    'retry_attempts' => env('BANQUE_CENTRALE_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('BANQUE_CENTRALE_RETRY_DELAY', 1000),
]; 