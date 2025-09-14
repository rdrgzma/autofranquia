<div class="max-w-lg">
    @if(session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="space-y-2">
        <select wire:model="type" class="w-full p-2 border rounded">
            <option value="entrada">Entrada</option>
            <option value="saida">Saída</option>
        </select>

        <input wire:model.defer="value" type="number" step="0.01" class="w-full p-2 border rounded" placeholder="Valor" />
        <input wire:model.defer="date" type="date" class="w-full p-2 border rounded" />
        <input wire:model.defer="description" class="w-full p-2 border rounded" placeholder="Descrição" />

        <div class="flex justify-end">
            <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
        </div>
    </div>
</div>
