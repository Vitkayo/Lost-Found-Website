<?php

namespace App\Http\Controllers;

use App\Models\Item;
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
        $validated = $request->validate($this->rules());
        if ($validated['status'] === 'found' && blank($validated['verification_answer'] ?? null)) {
            return back()->withErrors(['verification_answer' => 'Add the private verification answer for a found item.'])->withInput();
        }

        $items->create($validated, $request->file('image'), $request->user()->id);

        return redirect()
            ->route('board.index')
            ->with('success', 'Report submitted successfully!');
    }

    public function edit(Request $request, Item $item)
    {
        $this->authorizeOwner($request, $item);

        return view('report', ['editItem' => $item]);
    }

    public function update(Request $request, Item $item, ItemDataService $items)
    {
        $this->authorizeOwner($request, $item);
        $items->update($item, $request->validate($this->rules()), $request->file('image'));

        return redirect()->route('board.index')->with('success', 'Report updated successfully.');
    }

    public function destroy(Request $request, Item $item, ItemDataService $items)
    {
        $this->authorizeOwner($request, $item);
        $items->delete((string) $item->id);

        return redirect()->route('board.index')->with('success', 'Report deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:lost,found'],
            'category' => ['required', 'in:'.implode(',', array_keys(config('lostfound.categories')))],
            'created_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'contact_info' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'verification_question' => ['nullable', 'required_if:status,found', 'string', 'max:255'],
            'verification_answer' => ['nullable', 'string', 'max:255'],
            'hidden_details' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
        ];
    }

    private function authorizeOwner(Request $request, Item $item): void
    {
        abort_unless((int) $item->user_id === (int) $request->user()->id, 403);
    }
}
