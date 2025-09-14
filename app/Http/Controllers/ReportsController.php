<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function sales()
    {
        return view('reports.sales'); // @livewire('reports.sales-report')
    }
}
