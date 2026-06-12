<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\Data;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $companies_data = Data::$companies_data;
        $data = Contact::latest()
            ->get()
            ->mapWithKeys(function (Contact $contact) use ($companies_data) {
                return [
                    $contact->id => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'gender' => $contact->gender,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'address' => $contact->address,
                        'company' => $companies_data[$contact->company_id]['name'] ?? 'Unknown Company',
                    ],
                ];
            });

        return view('contacts.index', compact('data', 'companies_data'));
    }

    public function create()
    {
        $companies_data = Data::$companies_data;
        return view('contacts.create', compact('companies_data'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'in:' . implode(',', array_keys(Data::$companies_data))],
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index');
    }

    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        $companies_data = Data::$companies_data;
        $data = [
            'id' => $contact->id,
            'name' => $contact->name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'address' => $contact->address,
            'company' => $companies_data[$contact->company_id]['name'] ?? 'Unknown Company',
        ];

        return view('contacts.show', compact('data'));
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        $companies_data = Data::$companies_data;

        return view('contacts.edit', compact('contact', 'companies_data'));
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'in:' . implode(',', array_keys(Data::$companies_data))],
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index');
    }

    public function destroy($id)
    {
        Contact::findOrFail($id)->delete();
        return redirect()->route('contacts.index');
    }
}
