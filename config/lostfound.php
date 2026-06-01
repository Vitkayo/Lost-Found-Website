<?php

return [
    'admin_password' => env('LOSTFOUND_ADMIN_PASSWORD', 'RUPPSTAFF'),
    'items_file' => storage_path('app/items.json'),

    'categories' => [
        'electronic' => 'Electronics',
        'id_card' => 'Student ID',
        'wallet' => 'Wallet',
        'key' => 'Key',
        'book' => 'Books & Stationery',
        'clothes_accessories' => 'Clothing & Accessories',
        'other' => 'Other',
    ],

    /*
    | Default sort when opening a category (desc = newest first, asc = oldest first).
    */
    'category_default_sort' => [
        'all' => 'desc',
        'electronic' => 'desc',
        'id_card' => 'desc',
        'wallet' => 'desc',
        'key' => 'desc',
        'book' => 'asc',
        'clothes_accessories' => 'desc',
        'other' => 'desc',
    ],
];
