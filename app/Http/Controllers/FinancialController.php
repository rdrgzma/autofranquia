<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function index()
    {
        return view('financial.index'); // @livewire('financial.index')
    }

    public function create(Request $request)
    {
        return view('financial.form'); // ?id= para editar via Livewire
    }
}
