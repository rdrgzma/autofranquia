<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdvController extends Controller
{
    public function index()
    {
        return view('pdv.index'); // contém @livewire('pdv.checkout')
    }
}
