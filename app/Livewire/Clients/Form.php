<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Form extends Component
{
    use AuthorizesRequests;

    public $clientId;
    public $name;
    public $document;
    public $document_type = 'CPF';
    public $phone;
    public $email;
    public $vehicle;
    public $address = [];
    public $franchise_id;

    public function mount($id = null)
    {
        $this->franchise_id = auth()->user()->franchise_id;
        if ($id) {
            $c = Client::findOrFail($id);
            $this->clientId = $c->id;
            $this->name = $c->name;
            $this->document = $c->document;
            $this->document_type = $c->document_type;
            $this->phone = $c->phone;
            $this->email = $c->email;
            $this->vehicle = $c->vehicle;
            $this->address = $c->address ?? [];
            $this->franchise_id = $c->franchise_id;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:191',
            'document' => 'nullable|string|max:50',
            'document_type' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'vehicle' => 'nullable|string|max:191',
            'address.street' => 'nullable|string|max:191',
            'address.number' => 'nullable|string|max:20',
            'address.city' => 'nullable|string|max:100',
            'address.zip' => 'nullable|string|max:20',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'document' => $this->document,
            'document_type' => $this->document_type,
            'phone' => $this->phone,
            'email' => $this->email,
            'vehicle' => $this->vehicle,
            'address' => $this->address,
            'franchise_id' => $this->franchise_id,
        ];

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $this->authorize('update', $client);
            $client->update($data);
        } else {
            $this->authorize('create', Client::class);
            Client::create($data);
        }

        session()->flash('message', 'Cliente salvo.');
        $this->emit('clientSaved');
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.form');
    }
}
