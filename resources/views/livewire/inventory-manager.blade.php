<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <flux:heading size="xl">Gestão de Estoque</flux:heading>
            <flux:subheading>Controle completo do inventário {{ auth()->user()->role === 'super_admin' ? 'de todas as franquias' : 'da franquia' }}</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:button wire:click="generateStockReport" variant="ghost" icon="document-arrow-down">
                Exportar
            </flux:button>
            <flux:button href="{{ route('products.create') }}" variant="filled" icon="plus">
                Novo Produto
            </flux:button>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Total de Produtos</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_products'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <flux:icon.cube class="w-6 h-6 text-blue-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Estoque Baixo</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['low_stock'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                    <flux:icon.exclamation-triangle class="w-6 h-6 text-orange-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Sem Estoque</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['out_of_stock'] }}</p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
                    <flux:icon.x-circle class="w-6 h-6 text-red-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Valor Total</p>
                    <p class="text-2xl font-bold text-green-600">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <flux:icon.currency-dollar-sign class="w-6 h-6 text-green-600" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Busca --}}
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar produtos..."
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

            {{-- Filtro de Estoque --}}
            <div>
                <flux:select wire:model.live="stockFilter">
                    <option value="all">Todos os produtos</option>
                    <option value="low">Estoque baixo</option>
                    <option value="critical">Estoque crítico</option>
                    <option value="out">Sem estoque</option>
                </flux:select>
            </div>

            {{-- Ações rápidas --}}
            <div class="flex gap-2">
                <flux:button wire:click="$refresh" variant="ghost" size="sm" icon="arrow-path">
                    Atualizar
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Tabela de Inventário --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="text-left p-4 font-medium text-zinc-900 dark:text-white">Produto</th>
                    @if(auth()->user()->role === 'super_admin')
                        <th class="text-left p-4 font-medium text-zinc-900 dark:text-white">Franquia</th>
                    @endif
                    <th class="text-center p-4 font-medium text-zinc-900 dark:text-white">Estoque</th>
                    <th class="text-center p-4 font-medium text-zinc-900 dark:text-white">Mín./Localização</th>
                    <th class="text-center p-4 font-medium text-zinc-900 dark:text-white">Status</th>
                    <th class="text-center p-4 font-medium text-zinc-900 dark:text-white">Valor</th>
                    <th class="text-center p-4 font-medium text-zinc-900 dark:text-white">Ações</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($inventories as $inventory)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        {{-- Produto --}}
                        <td class="p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center">
                                    <flux:icon.cube class="w-5 h-5 text-zinc-500" />
                                </div>
                                <div>
                                    <h4 class="font-medium text-zinc-900 dark:text-white">{{ $inventory->product->name }}</h4>
                                    <div class="flex items-center space-x-2 text-sm text-zinc-500">
                                        @if($inventory->product->sku)
                                            <span>{{ $inventory->product->sku }}</span>
                                        @endif
                                        @if($inventory->product->category)
                                            <flux:badge variant="subtle" size="sm">{{ $inventory->product->category }}</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Franquia (apenas para super admin) --}}
                        @if(auth()->user()->role === 'super_admin')
                            <td class="p-4 text-sm">{{ $inventory->franchise->name }}</td>
                        @endif

                        {{-- Estoque --}}
                        <td class="p-4 text-center">
                            <div class="font-bold text-lg {{ $inventory->quantity <= 0 ? 'text-red-600' : ($inventory->quantity <= $inventory->min_stock ? 'text-orange-600' : 'text-green-600') }}">
                                {{ number_format($inventory->quantity, 0) }}
                            </div>
                            <div class="text-xs text-zinc-500">unidades</div>
                        </td>

                        {{-- Mínimo/Localização --}}
                        <td class="p-4 text-center text-sm">
                            <div class="text-zinc-600 dark:text-zinc-400">Mín: {{ $inventory->min_stock }}</div>
                            <div class="text-xs text-zinc-500">{{ $inventory->location ?? 'N/A' }}</div>
                        </td>

                        {{-- Status --}}
                        <td class="p-4 text-center">
                            @if($inventory->quantity <= 0)
                                <flux:badge variant="danger">Sem Estoque</flux:badge>
                            @elseif($inventory->quantity <= $inventory->min_stock * 0.5)
                                <flux:badge variant="danger">Crítico</flux:badge>
                            @elseif($inventory->quantity <= $inventory->min_stock)
                                <flux:badge variant="warning">Baixo</flux:badge>
                            @else
                                <flux:badge variant="success">OK</flux:badge>
                            @endif
                        </td>

                        {{-- Valor --}}
                        <td class="p-4 text-center">
                            <div class="font-medium">R$ {{ number_format($inventory->quantity * $inventory->product->price, 2, ',', '.') }}</div>
                            <div class="text-xs text-zinc-500">{{ number_format($inventory->product->price, 2, ',', '.') }} cada</div>
                        </td>

                        {{-- Ações --}}
                        <td class="p-4">
                            <div class="flex items-center justify-center space-x-1">
                                {{-- Entrada --}}
                                <flux:button
                                    wire:click="openMovementModal({{ $inventory->product->id }}, 'in')"
                                    variant="ghost"
                                    size="sm"
                                    icon="plus"
                                    title="Entrada de estoque"
                                >
                                </flux:button>

                                {{-- Saída --}}
                                <flux:button
                                    wire:click="openMovementModal({{ $inventory->product->id }}, 'out')"
                                    variant="ghost"
                                    size="sm"
                                    icon="minus"
                                    title="Saída de estoque"
                                >
                                </flux:button>

                                {{-- Ajuste --}}
                                <flux:button
                                    wire:click="openAdjustmentModal({{ $inventory->product->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil-square"
                                    title="Ajustar estoque"
                                >
                                </flux:button>

                                {{-- Transferir --}}
                                @if(auth()->user()->role === 'super_admin' || count($franchises) > 1)
                                    <flux:button
                                        wire:click="openTransferModal({{ $inventory->product->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="arrows-right-left"
                                        title="Transferir entre franquias"
                                    >
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'super_admin' ? '7' : '6' }}" class="p-12 text-center">
                            <flux:icon.cube class="w-16 h-16 mx-auto text-zinc-400 mb-4" />
                            <flux:heading size="lg" class="text-zinc-500 mb-2">Nenhum produto encontrado</flux:heading>
                            <flux:subheading class="text-zinc-400">
                                Ajuste os filtros ou adicione produtos ao inventário
                            </flux:subheading>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($inventories->hasPages())
            <div class="border-t border-zinc-200 dark:border-zinc-700 p-4">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de Movimentação --}}
    <flux:modal name="movement" :show="$showMovementModal">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">
                {{ $movementType === 'in' ? 'Entrada' : 'Saída' }} de Estoque
            </flux:heading>

            @if($selectedProduct)
                <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <h4 class="font-medium">{{ $selectedProduct->name }}</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedProduct->sku }}</p>
                </div>
            @endif

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Quantidade</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0" placeholder="0" autofocus />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Motivo</flux:label>
                    <flux:select wire:model="reason">
                        <option value="">Selecione o motivo</option>
                        @if($movementType === 'in')
                            <option value="Compra">Compra</option>
                            <option value="Devolução">Devolução</option>
                            <option value="Transferência">Transferência recebida</option>
                            <option value="Ajuste">Ajuste de inventário</option>
                        @else
                            <option value="Venda">Venda</option>
                            <option value="Perda">Perda/Avaria</option>
                            <option value="Transferência">Transferência enviada</option>
                            <option value="Uso interno">Uso interno</option>
                        @endif
                    </flux:select>
                    <flux:error name="reason" />
                </flux:field>

                <flux:field>
                    <flux:label>Observações</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Observações opcionais..." rows="3" />
                </flux:field>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <flux:button wire:click="$set('showMovementModal', false)" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="processMovement" variant="primary">
                    Confirmar {{ $movementType === 'in' ? 'Entrada' : 'Saída' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de Ajuste --}}
    <flux:modal name="adjustment" :show="$showAdjustmentModal">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Ajustar Inventário</flux:heading>

            @if($selectedProduct)
                <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <h4 class="font-medium">{{ $selectedProduct->name }}</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedProduct->sku }}</p>
                </div>
            @endif

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nova Quantidade</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Estoque Mínimo</flux:label>
                    <flux:input wire:model="newMinStock" type="number" min="0" placeholder="10" />
                    <flux:error name="newMinStock" />
                </flux:field>

                <flux:field>
                    <flux:label>Localização</flux:label>
                    <flux:input wire:model="newLocation" placeholder="ex: Estoque A1, Prateleira 2" />
                    <flux:error name="newLocation" />
                </flux:field>

                <flux:field>
                    <flux:label>Motivo do Ajuste</flux:label>
                    <flux:select wire:model="reason">
                        <option value="">Selecione o motivo</option>
                        <option value="Inventário físico">Inventário físico</option>
                        <option value="Correção de erro">Correção de erro</option>
                        <option value="Mudança de localização">Mudança de localização</option>
                        <option value="Ajuste de sistema">Ajuste de sistema</option>
                    </flux:select>
                    <flux:error name="reason" />
                </flux:field>

                <flux:field>
                    <flux:label>Observações</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Detalhes do ajuste..." rows="3" />
                </flux:field>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <flux:button wire:click="$set('showAdjustmentModal', false)" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="processAdjustment" variant="primary">
                    Confirmar Ajuste
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de Transferência --}}
    <flux:modal name="transfer" :show="$showTransferModal">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Transferir para Outra Franquia</flux:heading>

            @if($selectedProduct)
                <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <h4 class="font-medium">{{ $selectedProduct->name }}</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedProduct->sku }}</p>
                </div>
            @endif

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Franquia Destino</flux:label>
                    <flux:select wire:model="targetFranchise">
                        <option value="">Selecione a franquia</option>
                        @foreach($franchises->where('id', '!=', auth()->user()->franchise_id) as $franchise)
                            <option value="{{ $franchise->id }}">{{ $franchise->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="targetFranchise" />
                </flux:field>

                <flux:field>
                    <flux:label>Quantidade</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Motivo</flux:label>
                    <flux:input wire:model="reason" placeholder="ex: Reposição de estoque, Redistribuição" />
                    <flux:error name="reason" />
                </flux:field>

                <flux:field>
                    <flux:label>Observações</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Observações da transferência..." rows="3" />
                </flux:field>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <flux:button wire:click="$set('showTransferModal', false)" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="processTransfer" variant="primary">
                    Confirmar Transferência
                </flux:button>
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
