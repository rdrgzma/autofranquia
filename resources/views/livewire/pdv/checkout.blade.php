<div>
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar produto..." class="w-full p-2 border rounded mb-2">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" class="p-2 border rounded hover:bg-gray-100">
                        <div class="font-semibold">{{ $product->name }}</div>
                        <div class="text-sm">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="border p-4 rounded">
            <h3 class="font-bold mb-2">Carrinho</h3>
            @foreach($cart as $id => $item)
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <div>{{ $item['name'] }} x{{ $item['qty'] }}</div>
                        <div class="text-sm text-gray-600">R$ {{ number_format($item['price'] * $item['qty'], 2, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="removeFromCart({{ $id }})" class="text-red-500">x</button>
                    </div>
                </div>
            @endforeach
            <hr class="my-2">
            <div class="flex justify-between font-bold">
                <span>Total:</span>
                <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
            </div>
            <div class="mt-2">
                <label>Desconto:</label>
                <input wire:model.live="discount" type="number" step="0.01" class="w-full p-1 border rounded">
            </div>
            <div class="mt-2">
                <label>Forma de Pagamento:</label>
                <select wire:model="paymentMethod" class="w-full p-1 border rounded">
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Cartão">Cartão</option>
                    <option value="Pix">Pix</option>
                </select>
            </div>
            <button wire:click="checkout" class="mt-4 w-full bg-blue-500 text-white p-2 rounded">Finalizar Venda</button>
        </div>
    </div>
</div>
