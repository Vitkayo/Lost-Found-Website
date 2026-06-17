<?php

namespace App\Http\Controllers;

use App\Services\ItemDataService;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request, ItemDataService $items)
    {
        $filter = $request->query('status', 'all');
        $category = $request->query('category', 'all');
        $search = $request->query('search', '');
        $date = $request->query('date', '');
        $sort = $request->query('sort');

        if ($sort === null) {
            $sort = config('lostfound.category_default_sort.'.$category, 'desc');
        }

        return view('board', [
            'items' => $items->paginated([
                'status' => $filter,
                'category' => $category,
                'search' => $search,
                'date' => $date,
                'sort' => $sort,
            ], 12),
            'filter' => $filter,
            'category' => $category,
            'categories' => config('lostfound.categories'),
            'search' => $search,
            'date' => $date,
            'sort' => $sort,
        ]);
    }
}
