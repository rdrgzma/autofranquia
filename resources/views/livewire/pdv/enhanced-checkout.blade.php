<div class="h-screen flex flex-col bg-zinc-50 dark:bg-zinc-900">
    {{-- Header do PDV --}}
    <div class="bg-white dark:bg-zinc-800 shadow-sm border-b border-zinc-200 dark:border-zinc-700 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    PDV - {{ optional(auth()->user()->franchise)->name }}
                </flux:heading>
                <flux:badge variant="subtle">
                    {{ now()->format('d/m/Y H:i') }}
                </flux:badge>
            </div>

            <div class="flex items-center space-x-2">
                @if(count($cart) > 0)
                    <flux:button wire:click="clearCart" variant="ghost" size="sm" icon="trash">
                        Limpar
                    </flux:button>
                @endif
                <flux:button href="{{ route('dashboard') }}" variant="ghost" size="sm" icon="x-mark">
                    Sair
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Painel Esquerdo - Produtos e Busca --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Busca e Filtros --}}
            <div class="bg-white dark:bg-zinc-800 p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col lg:flex-row gap-4">
                    {{-- Busca Principal --}}
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar produtos (código, nome)..."
                            icon="magnifying-glass"
                            size="lg"
                        />
                    </div>

                    {{-- Filtro de Categoria --}}
                    <div class="w-full lg:w-48">
                        <flux:select wire:model.live="selectedCategory" size="lg">
                            <option value="all">Todas as categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Botão de Serviços --}}
                    <flux:button
                        wire:click="$set('showServicesModal', true)"
                        variant="filled"
                        size="lg"
                        icon="cog-6-tooth"
                    >
                        Serviços
                    </flux:button>
                </div>
            </div>

            {{-- Grid de Produtos --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse($products as $product)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                             wire:click="addToCart({{ $product->id }})">
                            {{-- Imagem --}}
                            <div class="aspect-square bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center">
                                @if($product->default_image)
                                    <img src="{{ $product->default_image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <flux:icon.cube class="w-8 h-8 text-zinc-400" />
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-3">
                                <h4 class="font-medium text-sm text-zinc-900 dark:text-white truncate">{{ $product->name }}</h4>
                                @if($product->sku)
                                    <p class="text-xs text-zinc-500 mb-1">{{ $product->sku }}</p>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-green-600">
                                        R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </span>
                                    @if($product->category)
                                        <flux:badge variant="subtle" size="sm">{{ $product->category }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <flux:icon.cube class="w-16 h-16 mx-auto text-zinc-400 mb-4" />
                            <p class="text-zinc-500">Nenhum produto encontrado</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Painel Direito - Carrinho e Checkout --}}
        <div class="w-96 bg-white dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 flex flex-col">
            {{-- Header do Carrinho --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Carrinho</flux:heading>
                @if(count($cart) > 0)
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ count($cart) }} itens</p>
                @endif
            </div>

            {{-- Cliente Selecionado --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                @if($selectedClient)
                    <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div>
                            <p class="font-medium text-blue-900 dark:text-blue-100">{{ $selectedClient->name }}</p>
                            @if($selectedClient->phone)
                                <p class="text-xs text-blue-600 dark:text-blue-300">{{ $selectedClient->phone }}</p>
                            @endif
                        </div>
                        <flux:button wire:click="$set('selectedClient', null)" variant="ghost" size="sm" icon="x-mark">
                        </flux:button>
                    </div>
                @else
                    <div>
                        <flux:input
                            wire:model.live.debounce.300ms="clientSearch"
                            placeholder="Buscar cliente..."
                            icon="user"
                        />

                        @if($clients && count($clients) > 0)
                            <div class="mt-2 max-h-32 overflow-y-auto border border-zinc-200 dark:border-zinc-600 rounded-lg">
                                @foreach($clients as $client)
                                    <div wire:click="selectClient({{ $client->id }})"
                                         class="p-2 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer border-b border-zinc-100 dark:border-zinc-600 last:border-b-0">
                                        <p class="font-medium">{{ $client->name }}</p>
                                        @if($client->phone)
                                            <p class="text-xs text-zinc-500">{{ $client->phone }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($clientSearch && strlen($clientSearch) >= 3)
                            <div class="mt-2">
                                <flux:button wire:click="createQuickClient" variant="ghost" size="sm" class="w-full">
                                    + Criar cliente "{{ $clientSearch }}"
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Itens do Carrinho --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($cart as $key => $item)
                    <div class="p-4 border-b border-zinc-100 dark:border-zinc-700">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-medium text-sm">{{ $item['name'] }}</h4>
                                @if($item['type'] === 'product' && isset($item['sku']))
                                    <p class="text-xs text-zinc-500">{{ $item['sku'] }}</p>
                                @endif
                                @if($item['type'] === 'service' && isset($item['duration']))
                                    <p class="text-xs text-zinc-500">{{ $item['duration'] }} min</p>
                                @endif
                            </div>
                            <flux:button wire:click="removeFromCart('{{ $key }}')" variant="ghost" size="sm" icon="trash">
                            </flux:button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <flux:button
                                    wire:click="updateQuantity('{{ $key }}', {{ $item['qty'] - 1 }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="minus"
                                >
                                </flux:button>
                                <span class="w-8 text-center font-medium">{{ $item['qty'] }}</span>
                                <flux:button
                                    wire:click="updateQuantity('{{ $key }}', {{ $item['qty'] + 1 }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="plus"
                                >
                                </flux:button>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-zinc-500">R$ {{ number_format($item['price'], 2, ',', '.') }} cada</p>
                                <p class="font-bold">R$ {{ number_format($item['price'] * $item['qty'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-zinc-500">
                        <flux:icon.shopping-cart class="w-12 h-12 mx-auto mb-2" />
                        <p>Carrinho vazio</p>
                        <p class="text-xs">Adicione produtos ou serviços</p>
                    </div>
                @endforelse
            </div>

            {{-- Totais e Checkout --}}
            @if(count($cart) > 0)
                <div class="border-t border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                    {{-- Desconto --}}
                    <div>
                        <flux:label>Desconto</flux:label>
                        <div class="flex items-center space-x-2">
                            <flux:select wire:model.live="discountType" class="w-20">
                                <option value="fixed">R$</option>
                                <option value="percentage">%</option>
                            </flux:select>
                            <flux:input
                                wire:model.lazy="discount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0,00"
                            />
                        </div>
                    </div>

                    {{-- Totais --}}
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Desconto:</span>
                                <span>- R$ {{ number_format($discountType === 'percentage' ? ($subtotal * $discount / 100) : $discount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-lg border-t border-zinc-200 dark:border-zinc-600 pt-2">
                            <span>Total:</span>
                            <span class="text-green-600">R$ {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Método de Pagamento --}}
                    <div>
                        <flux:label>Forma de Pagamento</flux:label>
                        <flux:select wire:model="paymentMethod">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Pix">Pix</option>
                            <option value="Transferência">Transferência</option>
                        </flux:select>
                    </div>

                    {{-- Valor Recebido (apenas para dinheiro) --}}
                    @if($paymentMethod === 'Dinheiro')
                        <div>
                            <flux:label>Valor Recebido</flux:label>
                            <flux:input
                                wire:model.lazy="receivedAmount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="{{ number_format($total, 2, ',', '.') }}"
                            />
                            @if($change > 0)
                                <p class="text-sm text-blue-600 mt-1">
                                    Troco: R$ {{ number_format($change, 2, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Observações --}}
                    <div>
                        <flux:label>Observações</flux:label>
                        <flux:textarea wire:model="notes" placeholder="Observações opcionais..." rows="2"></flux:textarea>
                    </div>

                    {{-- Botão Finalizar --}}
                    <flux:button
                        wire:click="checkout"
                        variant="primary"
                        size="lg"
                        class="w-full"
                        :disabled="$isProcessing"
                        icon="check"
                    >
                        @if($isProcessing)
                            Processando...
                        @else
                            Finalizar Venda
                        @endif
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Serviços --}}
    <flux:modal name="services" :show="$showServicesModal">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Serviços de Lavagem</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($availableServices as $service)
                    <div class="border border-zinc-200 dark:border-zinc-600 rounded-lg p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer"
                         wire:click="addService('{{ $service['id'] }}')">
                        <h4 class="font-medium">{{ $service['name'] }}</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $service['duration'] }} minutos</p>
                        <p class="font-bold text-green-600">R$ {{ number_format($service['price'], 2, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-4">
                <flux:button wire:click="$set('showServicesModal', false)" variant="ghost">
                    Fechar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

{{-- Notificações --}}
@if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('success') }}
    </div>
@endif

@if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
@endif

{{-- Scripts --}}
@script
<script>
    // Atalhos de teclado
    document.addEventListener('keydown', function(e) {
        // F1 - Focar na busca
        if (e.key === 'F1') {
            e.preventDefault();
            document.querySelector('input[placeholder*="Buscar produtos"]').focus();
        }

        // F2 - Limpar carrinho
        if (e.key === 'F2') {
            e.preventDefault();
            if (confirm('Limpar carrinho?')) {
                @this.clearCart();
            }
        }

        // F12 - Finalizar venda
        if (e.key === 'F12') {
            e.preventDefault();
            @this.checkout();
        }

        // ESC - Limpar busca
        if (e.key === 'Escape') {
            @this.set('search', '');
        }
    });

    // Listener para código de barras
    let barcode = '';
    let timeout;

    document.addEventListener('keypress', function(e) {
        // Se não estiver focado em um input
        if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            clearTimeout(timeout);
            barcode += String.fromCharCode(e.keyCode);

            timeout = setTimeout(() => {
                if (barcode.length > 4) { // Assumindo código de barras com mais de 4 caracteres
                    @this.call('addProductByBarcode', barcode);
                }
                barcode = '';
            }, 100);
        }
    });
</script>
@endscript
