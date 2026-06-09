<?php

return [
    'admin_password' => env('LOSTFOUND_ADMIN_PASSWORD', 'RUPPSTAFF'),
    'items_file' => storage_path('app/items.json'),

    'categories' => [
        'ticket' => 'Ticket',
        'id_card' => 'Card / ID',
        'bottle_umbrella' => 'Bottle / Umbrella',
        'electronic' => 'Electronics',
        'wallet' => 'Wallet / Money',
        'key' => 'Key',
        'book' => 'Books / Documents',
        'clothes_accessories' => 'Clothing & Accessories',
        'other' => 'Other',
    ],

    'category_search_aliases' => [
        'ticket' => ['ticket', 'exam ticket', 'parking ticket', 'receipt'],
        'id_card' => ['student id', 'card id', 'id card', 'bank card', 'library card', 'card'],
        'bottle_umbrella' => ['bottle', 'water bottle', 'umbrella'],
        'electronic' => ['electronics', 'phone', 'laptop', 'airpods', 'earphone', 'charger', 'calculator'],
        'wallet' => ['wallet', 'money', 'cash', 'purse'],
        'key' => ['key', 'keys'],
        'book' => ['book', 'document', 'notebook', 'paper', 'stationery'],
        'clothes_accessories' => ['clothing', 'accessory', 'bag', 'hat', 'jacket'],
        'other' => ['other'],
    ],

    /*
    | Default sort when opening a category (desc = newest first, asc = oldest first).
    */
    'category_default_sort' => [
        'all' => 'desc',
        'ticket' => 'desc',
        'bottle_umbrella' => 'desc',
        'electronic' => 'desc',
        'id_card' => 'desc',
        'wallet' => 'desc',
        'key' => 'desc',
        'book' => 'asc',
        'clothes_accessories' => 'desc',
        'other' => 'desc',
    ],
];
