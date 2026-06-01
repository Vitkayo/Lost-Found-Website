<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ItemDataService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request, ItemDataService $items)
    {
        return response()->json(
            $items->filtered([
                'status' => $request->query('status', 'all'),
                'category' => $request->query('category', 'all'),
                'search' => $request->query('search'),
                'date' => $request->query('date'),
                'sort' => $request->query('sort', 'desc'),
            ])
        );
    }

    public function store(Request $request, ItemDataService $items)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:lost,found'],
            'category' => ['required', 'in:'.implode(',', array_keys(config('lostfound.categories')))],
            'created_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'contact_info' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $item = $items->create($validated, $request->file('image'));

        return response()->json([
            'message' => 'Item reported successfully',
            'data' => $item,
        ], 201);
    }

    public function show(string $id, ItemDataService $items)
    {
        $item = $items->find($id);

        if (! $item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function destroy(string $id, ItemDataService $items)
    {
        if (! $items->delete($id)) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
