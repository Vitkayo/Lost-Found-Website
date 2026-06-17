<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        if (Item::count() > 0) {
            return;
        }

        $now = now();

        $samples = [
            [
                'title' => 'Blue Card ID',
                'status' => 'lost',
                'category' => 'id_card',
                'reported_at' => $now->copy()->subHours(2),
                'location' => 'Building A, Room 201',
                'contact_info' => 'Telegram: @rupp_student',
                'description' => 'RUPP logo lanyard, name on back.',
            ],
            [
                'title' => 'Black Umbrella',
                'status' => 'found',
                'category' => 'bottle_umbrella',
                'reported_at' => $now->copy()->subDay(),
                'location' => 'Library entrance',
                'contact_info' => '012 345 678',
                'description' => 'Compact foldable umbrella.',
            ],
            [
                'title' => 'Entrance Exam Ticket',
                'status' => 'found',
                'category' => 'ticket',
                'reported_at' => $now->copy()->subHours(3),
                'location' => 'Building A lobby',
                'contact_info' => 'Telegram: @rupp_found',
                'description' => 'Printed exam ticket found near the stairs.',
            ],
            [
                'title' => 'Wireless Mouse',
                'status' => 'found',
                'category' => 'electronic',
                'reported_at' => $now->copy()->subDays(3),
                'location' => 'Computer Lab 3',
                'contact_info' => 'Telegram: @campus_found',
                'description' => 'Logitech, no USB dongle in bag.',
            ],
        ];

        foreach ($samples as $sample) {
            Item::create($sample);
        }
    }
}
