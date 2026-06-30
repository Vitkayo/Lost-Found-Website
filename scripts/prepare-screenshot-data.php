<?php

declare(strict_types=1);

/**
 * Seed portfolio-friendly demo data for README screenshots.
 *
 * Usage:
 *   php scripts/prepare-screenshot-data.php
 *
 * Reads credentials from scripts/screenshot-config.json (copy from screenshot-config.example.json).
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Item;
use App\Models\ItemClaim;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$configPath = __DIR__.'/screenshot-config.json';

if (! is_file($configPath)) {
    fwrite(STDERR, "Missing {$configPath}\n");
    fwrite(STDERR, "Copy scripts/screenshot-config.example.json to scripts/screenshot-config.json first.\n");
    exit(1);
}

/** @var array<string, mixed> $config */
$config = json_decode((string) file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);

$userConfig = $config['user'] ?? null;
$adminConfig = $config['admin'] ?? null;

if (! is_array($userConfig) || ! is_array($adminConfig)) {
    fwrite(STDERR, "screenshot-config.json must include user and admin objects.\n");
    exit(1);
}

foreach (['user' => $userConfig, 'admin' => $adminConfig] as $label => $account) {
    foreach (['email', 'password', 'name'] as $field) {
        if (empty($account[$field])) {
            fwrite(STDERR, "Missing {$label}.{$field} in screenshot-config.json\n");
            exit(1);
        }
    }
}

$user = User::updateOrCreate(
    ['email' => $userConfig['email']],
    [
        'name' => $userConfig['name'],
        'password' => Hash::make($userConfig['password']),
        'role' => 'user',
        'status' => 'active',
        'phone' => '555-0100',
        'student_id' => 'STU-2026-0142',
    ],
);
$user->markEmailAsVerified();

$admin = User::updateOrCreate(
    ['email' => $adminConfig['email']],
    [
        'name' => $adminConfig['name'],
        'password' => Hash::make($adminConfig['password']),
        'role' => 'super_admin',
        'status' => 'active',
    ],
);
$admin->markEmailAsVerified();

Item::query()->delete();
ItemClaim::query()->delete();

$now = now();

$samples = [
    [
        'user_id' => $user->id,
        'title' => 'Blue Student ID Card',
        'status' => 'lost',
        'category' => 'id_card',
        'reported_at' => $now->copy()->subHours(3),
        'location' => 'Science Building, Room 204',
        'contact_info' => 'campusfound.demo@example.com',
        'description' => 'Blue lanyard with university logo. Name printed on the back.',
        'verification_question' => 'What name is printed on the card?',
        'verification_answer_hash' => Hash::make('Alex Rivera'),
    ],
    [
        'user_id' => $user->id,
        'title' => 'MacBook Pro Charger',
        'status' => 'lost',
        'category' => 'electronic',
        'reported_at' => $now->copy()->subDay(),
        'location' => 'Library, 2nd floor study area',
        'contact_info' => 'campusfound.demo@example.com',
        'description' => 'MagSafe USB-C adapter in a gray sleeve.',
    ],
    [
        'title' => 'Black Compact Umbrella',
        'status' => 'found',
        'category' => 'bottle_umbrella',
        'reported_at' => $now->copy()->subHours(8),
        'location' => 'Main entrance, Building A',
        'contact_info' => '555-0199',
        'description' => 'Foldable umbrella left near the security desk.',
    ],
    [
        'title' => 'Wireless Mouse (Logitech)',
        'status' => 'found',
        'category' => 'electronic',
        'reported_at' => $now->copy()->subDays(2),
        'location' => 'Computer Lab 3',
        'contact_info' => '555-0188',
        'description' => 'Gray wireless mouse without the USB receiver.',
    ],
    [
        'title' => 'Calculus Textbook',
        'status' => 'found',
        'category' => 'book',
        'reported_at' => $now->copy()->subHours(14),
        'location' => 'Cafeteria seating area',
        'contact_info' => '555-0177',
        'description' => 'Hardcover textbook with sticky notes in chapters 4–6.',
    ],
    [
        'title' => 'Entrance Exam Ticket',
        'status' => 'found',
        'category' => 'ticket',
        'reported_at' => $now->copy()->subHours(5),
        'location' => 'Registrar lobby',
        'contact_info' => '555-0166',
        'description' => 'Printed exam ticket found near the front desk.',
    ],
    [
        'title' => 'Brown Leather Wallet',
        'status' => 'lost',
        'category' => 'wallet',
        'reported_at' => $now->copy()->subDays(4),
        'location' => 'Student union cafeteria',
        'contact_info' => 'campusfound.demo@example.com',
        'description' => 'Contains student ID and cafeteria card.',
    ],
    [
        'title' => 'Dorm Room Key Set',
        'status' => 'lost',
        'category' => 'key',
        'reported_at' => $now->copy()->subHours(20),
        'location' => 'North Residence Hall bus stop',
        'contact_info' => 'campusfound.demo@example.com',
        'description' => 'Three keys on a blue keychain.',
    ],
];

foreach ($samples as $sample) {
    Item::create($sample);
}

$foundItem = Item::where('status', 'found')->where('title', 'Black Compact Umbrella')->first();

if ($foundItem) {
    ItemClaim::create([
        'item_id' => $foundItem->id,
        'user_id' => $user->id,
        'type' => 'claim',
        'status' => 'pending',
        'claimant_name' => $user->name,
        'contact_info' => 'campusfound.demo@example.com',
        'message' => 'I lost a similar umbrella yesterday near Building A.',
    ]);
}

echo "Screenshot demo data ready.\n";
echo "  User:  {$user->email}\n";
echo "  Admin: {$admin->email}\n";
echo '  Items: '.Item::count()."\n";
echo '  Claims: '.ItemClaim::count()."\n";
