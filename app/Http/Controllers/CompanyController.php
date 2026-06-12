<?php

namespace App\Http\Controllers;

use App\Services\Data;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies_data = Data::$companies_data;
        return response()->json($companies_data);
    }

    public function show(string $id)
    {
        $companies_data = Data::$companies_data;
        if (isset($companies_data[$id])) {
            return response()->json($companies_data[$id]);
        }

        return response()->json(['error' => 'Company not found'], 404);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
