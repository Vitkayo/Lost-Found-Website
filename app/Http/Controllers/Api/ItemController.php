<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
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
            'verification_question' => ['nullable', 'required_if:status,found', 'string', 'max:255'],
            'verification_answer' => ['nullable', 'string', 'max:255'],
            'hidden_details' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
        ]);

        $item = $items->create($validated, $request->file('image'), $request->user()->id);

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

    public function update(Request $request, string $id, ItemDataService $items)
    {
        $item = Item::findOrFail($id);
        abort_unless((int) $item->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:lost,found'],
            'category' => ['required', 'in:'.implode(',', array_keys(config('lostfound.categories')))],
            'created_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'contact_info' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'verification_question' => ['nullable', 'required_if:status,found', 'string', 'max:255'],
            'verification_answer' => ['nullable', 'string', 'max:255'],
            'hidden_details' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
        ]);

        return response()->json([
            'message' => 'Item updated successfully',
            'data' => $items->update($item, $validated, $request->file('image')),
        ]);
    }

    public function destroy(Request $request, string $id, ItemDataService $items)
    {
        $item = Item::find($id);
        if (! $item) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        abort_unless((int) $item->user_id === (int) $request->user()->id, 403);

        if (! $items->delete($id)) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
