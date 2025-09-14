<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';

    protected $listeners = ['clientSaved' => '$refresh'];

    public function render()
    {
        $query = Client::query();

        if (auth()->user()->role !== 'super_admin') {
            $query->where('franchise_id', auth()->user()->franchise_id);
        }

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('document', 'like', "%{$this->search}%");
        }

        $clients = $query->orderBy('name')->paginate(12);

        return view('livewire.clients.index', compact('clients'));
    }

    public function delete($id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);
        $client->delete();
        session()->flash('message', 'Cliente removido.');
        $this->emit('clientSaved');
    }
}
