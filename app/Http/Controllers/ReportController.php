<?php

namespace App\Http\Controllers;

use App\Services\ItemDataService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create()
    {
        return view('report');
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

        $items->create($validated, $request->file('image'));

        return redirect()
            ->route('board.index')
            ->with('success', 'Report submitted successfully!');
    }
}
