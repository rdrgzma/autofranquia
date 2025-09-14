<div class="max-w-2xl">
    @if(session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="space-y-2">
        <input wire:model.defer="name" class="w-full p-2 border rounded" placeholder="Nome" />
        <div class="grid grid-cols-2 gap-2">
            <input wire:model.defer="document" class="p-2 border rounded" placeholder="Documento" />
            <select wire:model.defer="document_type" class="p-2 border rounded">
                <option value="CPF">CPF</option>
                <option value="CNPJ">CNPJ</option>
            </select>
        </div>
        <input wire:model.defer="phone" class="w-full p-2 border rounded" placeholder="Telefone" />
        <input wire:model.defer="email" class="w-full p-2 border rounded" placeholder="E-mail" />
        <input wire:model.defer="vehicle" class="w-full p-2 border rounded" placeholder="Veículo (opcional)" />

        <div class="grid grid-cols-2 gap-2">
            <input wire:model.defer="address.street" class="p-2 border rounded" placeholder="Rua" />
            <input wire:model.defer="address.number" class="p-2 border rounded" placeholder="Número" />
            <input wire:model.defer="address.city" class="p-2 border rounded" placeholder="Cidade" />
            <input wire:model.defer="address.zip" class="p-2 border rounded" placeholder="CEP" />
        </div>

        <div class="flex justify-end">
            <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
        </div>
    </div>
</div>
