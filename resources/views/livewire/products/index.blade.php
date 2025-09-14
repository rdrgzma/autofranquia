<div>
    <div class="flex justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar produto..." class="p-2 border rounded">
        @can('create', \App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Novo Produto</a>
        @endcan
    </div>

    <table class="min-w-full border">
        <thead>
        <tr class="bg-gray-100">
            <th class="border p-2">Nome</th>
            <th class="border p-2">SKU</th>
            <th class="border p-2">Categoria</th>
            <th class="border p-2">Preço</th>
            <th class="border p-2">Ações</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td class="border p-2">{{ $product->name }}</td>
                <td class="border p-2">{{ $product->sku }}</td>
                <td class="border p-2">{{ $product->category }}</td>
                <td class="border p-2">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                <td class="border p-2">
                    @can('update', $product)
                        <a href="{{ route('products.edit', $product->id) }}" class="text-blue-500">Editar</a>
                    @endcan
                    @can('delete', $product)
                        <button wire:click="delete({{ $product->id }})" class="text-red-500 ml-2">Excluir</button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $products->links() }}
</div>
