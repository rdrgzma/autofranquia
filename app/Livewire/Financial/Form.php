<?php

namespace App\Livewire\Financial;

use Livewire\Component;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Form extends Component
{
    use AuthorizesRequests;

    public $transactionId;
    public $type = 'entrada';
    public $value;
    public $description;
    public $date;
    public $franchise_id;

    public function mount($id = null)
    {
        $this->franchise_id = auth()->user()->franchise_id;
        $this->date = now()->toDateString();

        if ($id) {
            $t = FinancialTransaction::findOrFail($id);
            $this->transactionId = $t->id;
            $this->type = $t->type;
            $this->value = $t->value;
            $this->description = $t->description;
            $this->date = $t->date;
            $this->franchise_id = $t->franchise_id;
        }
    }

    protected $rules = [
        'type' => 'required|in:entrada,saida',
        'value' => 'required|numeric',
        'description' => 'nullable|string|max:255',
        'date' => 'required|date',
    ];

    public function save()
    {
        $this->validate();

        $this->authorize('create', FinancialTransaction::class);

        $data = [
            'type' => $this->type,
            'value' => $this->value,
            'description' => $this->description,
            'date' => $this->date,
            'franchise_id' => $this->franchise_id,
            'created_by' => auth()->id(),
        ];

        if ($this->transactionId) {
            $tx = FinancialTransaction::findOrFail($this->transactionId);
            $this->authorize('update', $tx);
            $tx->update($data);
        } else {
            FinancialTransaction::create($data);
        }

        session()->flash('message','Lançamento salvo.');
        return redirect()->route('financial.index');
    }

    public function render()
    {
        return view('livewire.financial.form');
    }
}
