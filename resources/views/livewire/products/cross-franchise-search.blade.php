<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <flux:heading size="xl">Buscar Produtos nas Franquias</flux:heading>
            <flux:subheading>Encontre produtos disponíveis em outras unidades da rede</flux:subheading>
        </div>

        {{-- Solicitações Pendentes --}}
        @if(count($requestedProducts) > 0)
            <flux:modal.trigger name="requested-products">
                <flux:button variant="filled" icon="shopping-bag">
                    Solicitações ({{ count($requestedProducts) }})
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Busca --}}
            <div class="lg:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por produto, SKU ou franquia..."
                    icon="magnifying-glass"
                />
            </div>

            {{-- Categoria --}}
            <div>
                <flux:select wire:model.live="selectedCategory" placeholder="Todas as categorias">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Franquia --}}
            <div>
                <flux:select wire:model.live="selectedFranchise" placeholder="Todas as franquias">
                    <option value="">Todas as franquias</option>
                    @foreach($franchises as $franchise)
                        <option value="{{ $franchise->id }}">{{ $franchise->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Filtros Adicionais --}}
            <div class="flex items-center space-x-4">
                <flux:checkbox wire:model.live="showOnlyAvailable" label="Apenas disponíveis" />
            </div>
        </div>
    </div>

    {{-- Lista de Produtos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                {{-- Imagem do produto --}}
                <div class="aspect-square bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                    @if($product->default_image)
                        <img src="{{ $product->default_image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <flux:icon.cube class="w-16 h-16 text-zinc-400" />
                    @endif
                </div>

                {{-- Conteúdo --}}
                <div class="p-4">
                    {{-- Info do produto --}}
                    <div class="mb-3">
                        <h3 class="font-semibold text-zinc-900 dark:text-white truncate">{{ $product->name }}</h3>
                        @if($product->sku)
                            <p class="text-xs text-zinc-500 mb-1">SKU: {{ $product->sku }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-green-600">
                                R$ {{ number_format($product->price, 2, ',', '.') }}
                            </span>
                            @if($product->category)
                                <flux:badge variant="subtle" size="sm">{{ $product->category }}</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Info da franquia --}}
                    <div class="mb-3 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $product->franchise_name }}
                            </span>
                            <span class="text-xs text-zinc-500">
                                {{ $product->location ?? 'Estoque geral' }}
                            </span>
                        </div>

                        {{-- Estoque --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">Estoque:</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-1 {{ $product->quantity > 10 ? 'bg-green-500' : ($product->quantity > 0 ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
                                <span class="text-sm font-medium {{ $product->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $product->quantity }} unid.
                                </span>
                            </div>
                        </div>

                        {{-- Telefone da franquia --}}
                        @if($product->franchise_phone)
                            <div class="mt-1 text-xs text-zinc-500">
                                <flux:icon.phone class="inline w-3 h-3 mr-1" />
                                {{ $product->franchise_phone }}
                            </div>
                        @endif
                    </div>

                    {{-- Ações --}}
                    <div class="space-y-2">
                        @php $requestKey = $product->id . '_' . $product->franchise_id; @endphp

                        @if(isset($requestedProducts[$requestKey]))
                            {{-- Já solicitado --}}
                            <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <span class="text-sm text-blue-700 dark:text-blue-300">✓ Solicitado</span>
                                <flux:button
                                    wire:click="removeRequest('{{ $requestKey }}')"
                                    size="sm"
                                    variant="ghost"
                                    icon="x-mark"
                                >
                                    Cancelar
                                </flux:button>
                            </div>
                        @else
                            {{-- Disponível para solicitação --}}
                            @if($product->quantity > 0)
                                <flux:button
                                    wire:click="requestProduct({{ $product->id }}, {{ $product->franchise_id }})"
                                    variant="filled"
                                    size="sm"
                                    class="w-full"
                                    icon="plus"
                                >
                                    Solicitar Transferência
                                </flux:button>
                            @else
                                <flux:button disabled variant="ghost" size="sm" class="w-full">
                                    Sem Estoque
                                </flux:button>
                            @endif
                        @endif

                        {{-- Contato direto --}}
                        @if($product->franchise_phone)
                            <flux:button
                                href="tel:{{ $product->franchise_phone }}"
                                variant="ghost"
                                size="sm"
                                class="w-full"
                                icon="phone"
                            >
                                Ligar para Franquia
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-4 text-center py-12">
                <flux:icon.magnifying-glass class="w-16 h-16 mx-auto text-zinc-400 mb-4" />
                <flux:heading size="lg" class="text-zinc-500 mb-2">Nenhum produto encontrado</flux:heading>
                <flux:subheading class="text-zinc-400">
                    Tente ajustar os filtros ou buscar por outros termos
                </flux:subheading>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($products->hasPages())
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @endif

    {{-- Modal de Solicitações Pendentes --}}
    <flux:modal name="requested-products" class="max-w-4xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Solicitações de Transferência</flux:heading>

            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($requestedProducts as $key => $request)
                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-medium">{{ $request['product_name'] }}</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                De: {{ $request['franchise_name'] }} |
                                Quantidade: {{ $request['requested_quantity'] }} |
                                Solicitado em: {{ \Carbon\Carbon::parse($request['requested_at'])->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <flux:badge variant="subtle">Pendente</flux:badge>
                            <flux:button
                                wire:click="removeRequest('{{ $key }}')"
                                size="sm"
                                variant="danger"
                                icon="trash"
                            >
                                Remover
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500">
                        <p>Nenhuma solicitação pendente</p>
                    </div>
                @endforelse
            </div>

            <div class="flex justify-end mt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Fechar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

{{-- Notificações --}}
@if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('success') }}
    </div>
@endif

@if (session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
@endif
