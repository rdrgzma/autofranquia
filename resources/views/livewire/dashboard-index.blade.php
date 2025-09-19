<div class="space-y-6">
    {{-- Header com filtros e período --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <flux:heading size="xl" class="text-zinc-900 dark:text-white">
                Dashboard {{ auth()->user()->role === 'super_admin' ? 'Geral' : optional(auth()->user()->franchise)->name }}
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                Visão geral do desempenho em tempo real
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:select wire:model.live="selectedPeriod" variant="subtle">
                <option value="today">Hoje</option>
                <option value="7days">7 dias</option>
                <option value="30days">30 dias</option>
                <option value="90days">90 dias</option>
            </flux:select>

            <flux:button wire:click="loadStats" variant="ghost" size="sm" icon="arrow-path">
                Atualizar
            </flux:button>
        </div>
    </div>

    {{-- Cards de KPIs Principais --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Faturamento --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Faturamento</p>
                    <p class="text-2xl font-bold">
                        R$ {{ number_format($realTimeStats['revenue']['current'] ?? 0, 2, ',', '.') }}
                    </p>
                    @if(isset($realTimeStats['revenue']['previous']))
                        @php
                            $change = $realTimeStats['revenue']['previous'] > 0
                                ? (($realTimeStats['revenue']['current'] - $realTimeStats['revenue']['previous']) / $realTimeStats['revenue']['previous']) * 100
                                : 0;
                        @endphp
                        <div class="flex items-center mt-2">
                            <flux:icon.{{ $change >= 0 ? 'trending-up' : 'trending-down' }} class="w-4 h-4 mr-1" />
                            <span class="text-sm">{{ number_format(abs($change), 1) }}%</span>
                        </div>
                    @endif
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <flux:icon.currency-dollar-sign class="w-8 h-8" />
                </div>
            </div>

            {{-- Barra de progresso para meta --}}
            @if(isset($realTimeStats['revenue']['target']) && $realTimeStats['revenue']['target'] > 0)
                @php $progress = min(($realTimeStats['revenue']['current'] / $realTimeStats['revenue']['target']) * 100, 100); @endphp
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-blue-100 mb-1">
                        <span>Meta do período</span>
                        <span>{{ number_format($progress, 0) }}%</span>
                    </div>
                    <div class="w-full bg-blue-400/30 rounded-full h-2">
                        <div class="bg-white/80 h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Vendas --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Vendas</p>
                    <p class="text-2xl font-bold">{{ $realTimeStats['sales_count']['current'] ?? 0 }}</p>
                    @if(isset($realTimeStats['sales_count']['previous']))
                        @php
                            $change = $realTimeStats['sales_count']['previous'] > 0
                                ? (($realTimeStats['sales_count']['current'] - $realTimeStats['sales_count']['previous']) / $realTimeStats['sales_count']['previous']) * 100
                                : 0;
                        @endphp
                        <div class="flex items-center mt-2">
                            <flux:icon.{{ $change >= 0 ? 'trending-up' : 'trending-down' }} class="w-4 h-4 mr-1" />
                            <span class="text-sm">{{ number_format(abs($change), 1) }}%</span>
                        </div>
                    @endif
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <flux:icon.shopping-cart class="w-8 h-8" />
                </div>
            </div>
        </div>

        {{-- Ticket Médio --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Ticket Médio</p>
                    <p class="text-2xl font-bold">
                        R$ {{ number_format($realTimeStats['avg_ticket']['current'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <flux:icon.chart-bar class="w-8 h-8" />
                </div>
            </div>
        </div>

        {{-- Produtos em Baixo Estoque --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Estoque Baixo</p>
                    <p class="text-2xl font-bold">{{ count($realTimeStats['low_stock'] ?? []) }}</p>
                    <p class="text-xs text-orange-100 mt-1">produtos precisam reposição</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <flux:icon.exclamation-triangle class="w-8 h-8" />
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos e Análises --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Gráfico de Vendas Diárias --}}
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Vendas por Dia</flux:heading>
                <flux:badge variant="subtle">{{ $selectedPeriod }}</flux:badge>
            </div>

            <div class="h-64" x-data="salesChart(@js($realTimeStats['daily_sales'] ?? []))">
                <canvas x-ref="chart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Top Produtos --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
            <flux:heading size="lg" class="mb-4">Top Produtos</flux:heading>

            <div class="space-y-3">
                @forelse($realTimeStats['top_products'] ?? [] as $index => $product)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $product->total_qty }} vendidos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">R$ {{ number_format($product->total_revenue, 2, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500">
                        <flux:icon.inbox class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Nenhuma venda no período</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Alertas de Estoque e Métodos de Pagamento --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Alertas de Estoque Baixo --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Alertas de Estoque</flux:heading>
                @if(count($realTimeStats['low_stock'] ?? []) > 0)
                    <flux:badge variant="danger">{{ count($realTimeStats['low_stock']) }} produtos</flux:badge>
                @endif
            </div>

            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($realTimeStats['low_stock'] ?? [] as $item)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                        <div>
                            <p class="font-medium text-red-900 dark:text-red-100">{{ $item->product->name }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">
                                Estoque: {{ $item->quantity }} | Mínimo: {{ $item->min_stock }}
                            </p>
                        </div>
                        <flux:button href="/products" size="sm" variant="danger">
                            Repor
                        </flux:button>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500">
                        <flux:icon.check-circle class="w-12 h-12 mx-auto mb-2 text-green-500" />
                        <p>Todos os produtos com estoque adequado</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Métodos de Pagamento --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
            <flux:heading size="lg" class="mb-4">Métodos de Pagamento</flux:heading>

            <div class="space-y-4">
                @php $totalPayments = collect($realTimeStats['payment_methods'] ?? [])->sum('total'); @endphp
                @forelse($realTimeStats['payment_methods'] ?? [] as $payment)
                    @php $percentage = $totalPayments > 0 ? ($payment->total / $totalPayments) * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">{{ $payment->payment_method }}</span>
                            <span class="text-sm text-zinc-600">{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-zinc-500 mt-1">
                            <span>{{ $payment->count }} transações</span>
                            <span>R$ {{ number_format($payment->total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500">
                        <p>Nenhuma venda no período</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Performance das Franquias (apenas para super admin) --}}
    @if(auth()->user()->role === 'super_admin' && isset($realTimeStats['franchise_performance']))
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6">
            <flux:heading size="lg" class="mb-4">Performance das Franquias</flux:heading>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="text-left py-2 font-medium">Franquia</th>
                        <th class="text-right py-2 font-medium">Faturamento</th>
                        <th class="text-right py-2 font-medium">Vendas</th>
                        <th class="text-right py-2 font-medium">Ticket Médio</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($realTimeStats['franchise_performance'] as $franchise)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3">
                                <div class="font-medium">{{ $franchise->franchise->name ?? 'Franquia #' . $franchise->franchise_id }}</div>
                            </td>
                            <td class="text-right py-3 font-bold text-green-600">
                                R$ {{ number_format($franchise->total_revenue, 2, ',', '.') }}
                            </td>
                            <td class="text-right py-3">{{ $franchise->total_sales }}</td>
                            <td class="text-right py-3">R$ {{ number_format($franchise->avg_ticket, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Script para gráficos --}}
@script
<script>
    Alpine.data('salesChart', (data) => ({
        init() {
            const ctx = this.$refs.chart.getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.date),
                    datasets: [{
                        label: 'Faturamento',
                        data: data.map(item => item.revenue),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        }
    }));
</script>
@endscript
