<?php

namespace App\Http\Controllers;

use App\Services\ClaimDataService;
use App\Services\ItemDataService;

class HomeController extends Controller
{
    public function __invoke(ItemDataService $items, ClaimDataService $claims)
    {
        return view('home', [
            'recentItems' => $items->recent(6),
            'recentClaims' => $claims->recent(4),
            'categories' => config('lostfound.categories'),
            'categoryCounts' => $items->categoryCounts(),
            'stats' => $items->stats(),
        ]);
    }
}
