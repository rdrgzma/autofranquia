<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        return view('products.index'); // @livewire('products.index')
    }

    public function create()
    {
        return view('products.form'); // formulário de cadastro/edição (implemente se necessário)
    }
}
