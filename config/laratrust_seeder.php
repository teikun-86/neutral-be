<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => true,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'super administrator' => [
            'dashboard' => 'r',
            'users' => 'c,r,u,d',
            'payments' => 'c,r,u,d',
            'haji-umrah' => 'r',
            'haji-umrah.flight' => 'c,r,u,d',
            'haji-umrah.hotel' => 'c,r,u,d',
            'haji-umrah.package' => 'c,r,u,d',
            'haji-umrah.flight.reservation' => 'c,r,u,d,ap',
            'haji-umrah.hotel.reservation' => 'c,r,u,d,ap',
            'haji-umrah.package.reservation' => 'c,r,u,d,ap',
            'roles' => 'c,r,u,d',
        ],
        'admin 1' => [
            'dashboard' => 'r',
            'payments' => 'c,r',
            'haji-umrah' => 'r',
            'haji-umrah.flight' => 'c,r,u',
            'haji-umrah.hotel' => 'c,r,u',
            'haji-umrah.package' => 'c,r,u',
            'haji-umrah.flight.reservation' => 'c,r,u,ap',
            'haji-umrah.hotel.reservation' => 'c,r,u,ap',
            'haji-umrah.package.reservation' => 'c,r,u,ap',
        ],
        'admin 2' => [
            'dashboard' => 'r',
            'haji-umrah' => 'r',
            'haji-umrah.flight' => 'c,r,u',
            'haji-umrah.hotel' => 'c,r,u',
            'haji-umrah.package' => 'c,r,u',
        ],
        'admin 3' => [
            'dashboard' => 'r',
            'haji-umrah' => 'r',
            'haji-umrah.flight' => 'r',
            'haji-umrah.hotel' => 'r',
            'haji-umrah.package' => 'r',
            'haji-umrah.flight.reservation' => 'c,r,u,ap',
            'haji-umrah.hotel.reservation' => 'c,r,u,ap',
            'haji-umrah.package.reservation' => 'c,r,u,ap',
        ]
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'ds' => 'delete-self',
        'ap' => 'add-payment'
    ]
];
