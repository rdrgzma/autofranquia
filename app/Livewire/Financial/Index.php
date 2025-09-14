<?php

namespace App\Livewire\Financial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $type = '';

    public function render()
    {
        $query = FinancialTransaction::query();

        if (auth()->user()->role !== 'super_admin') {
            $query->where('franchise_id', auth()->user()->franchise_id);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        $transactions = $query->orderBy('date','desc')->paginate(15);

        return view('livewire.financial.index', compact('transactions'));
    }

    public function delete($id)
    {
        $tx = FinancialTransaction::findOrFail($id);
        $this->authorize('delete', $tx);
        $tx->delete();
        session()->flash('message','Lançamento removido.');
    }
}
