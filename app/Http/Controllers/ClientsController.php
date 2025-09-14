<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function index()
    {
        return view('clients.index'); // @livewire('clients.index')
    }

    public function create(Request $request)
    {
        // aceita parâmetro ?id= para edição via Livewire mount
        return view('clients.form');
    }
}
