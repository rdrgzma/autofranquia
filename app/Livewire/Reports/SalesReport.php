<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Franchise;

class SalesReport extends Component
{
    public $start;
    public $end;
    public $franchise_id;

    public function mount()
    {
        $this->end = now()->toDateString();
        $this->start = now()->subMonth()->toDateString();
        if (auth()->user()->role !== 'super_admin') {
            $this->franchise_id = auth()->user()->franchise_id;
        }
    }

    public function generate()
    {
        // re-render
    }

    public function render()
    {
        $query = Sale::query();

        if ($this->start) {
            $query->where('date', '>=', $this->start);
        }
        if ($this->end) {
            $query->where('date', '<=', $this->end);
        }
        if ($this->franchise_id) {
            $query->where('franchise_id', $this->franchise_id);
        } else if (auth()->user()->role !== 'super_admin') {
            $query->where('franchise_id', auth()->user()->franchise_id);
        }

        $sales = $query->with('user','franchise')->orderBy('date','desc')->limit(100)->get();

        $total = $sales->sum('total');

        $franchises = Franchise::orderBy('name')->get();

        return view('livewire.reports.sales-report', compact('sales','total','franchises'));
    }
}
