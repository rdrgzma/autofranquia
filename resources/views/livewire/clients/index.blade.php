<div>
    <div class="flex justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" class="p-2 border rounded" placeholder="Buscar cliente...">
        <a href="{{ route('clients.create') }}" class="bg-blue-500 text-white px-3 py-1 rounded">Novo Cliente</a>
    </div>

    <table class="min-w-full border">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border">Nome</th>
            <th class="p-2 border">Documento</th>
            <th class="p-2 border">Telefone</th>
            <th class="p-2 border">Franquia</th>
            <th class="p-2 border">Ações</th>
        </tr>
        </thead>
        <tbody>
        @foreach($clients as $client)
            <tr>
                <td class="p-2 border">{{ $client->name }}</td>
                <td class="p-2 border">{{ $client->document }}</td>
                <td class="p-2 border">{{ $client->phone }}</td>
                <td class="p-2 border">{{ optional($client->franchise)->name }}</td>
                <td class="p-2 border">
                    @can('update', $client)
                        <a href="{{ route('clients.create', ['id' => $client->id]) }}" class="text-blue-500">Editar</a>
                    @endcan
                    @can('delete', $client)
                        <button wire:click="delete({{ $client->id }})" class="text-red-500 ml-2">Excluir</button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $clients->links() }}
</div>
