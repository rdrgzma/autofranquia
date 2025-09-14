<div>
    <div class="flex justify-between mb-4">
        <div class="flex gap-2">
            <input wire:model.live.debounce.300ms="search" class="p-2 border rounded" placeholder="Buscar descrição..." />
            <select wire:model="type" class="p-2 border rounded">
                <option value="">Todos</option>
                <option value="entrada">Entrada</option>
                <option value="saida">Saída</option>
            </select>
        </div>
        <a href="{{ route('financial.create') }}" class="bg-blue-500 text-white px-3 py-1 rounded">Novo Lançamento</a>
    </div>

    @if(session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('message') }}</div>
    @endif

    <table class="min-w-full border">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border">Data</th>
            <th class="p-2 border">Descrição</th>
            <th class="p-2 border">Tipo</th>
            <th class="p-2 border">Valor</th>
            <th class="p-2 border">Ações</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $t)
            <tr>
                <td class="p-2 border">{{ $t->date }}</td>
                <td class="p-2 border">{{ $t->description }}</td>
                <td class="p-2 border">{{ $t->type }}</td>
                <td class="p-2 border">R$ {{ number_format($t->value,2,',','.') }}</td>
                <td class="p-2 border">
                    @can('update', $t)
                        <a href="{{ route('financial.create', ['id' => $t->id]) }}" class="text-blue-500">Editar</a>
                    @endcan

                    @can('delete', $t)
                        <button wire:click="delete({{ $t->id }})" class="text-red-500 ml-2">Excluir</button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $transactions->links() }}
</div>
