{{-- resources/views/components/layouts/app/sidebar.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    {{-- Logo --}}
    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600 text-white">
            <svg class="size-5 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <div class="ms-1 grid flex-1 text-start text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">WashPro ERP</span>
            <span class="text-xs text-zinc-500">{{ optional(auth()->user()->franchise)->name ?? 'Sistema' }}</span>
        </div>
    </a>

    {{-- Navegação Principal --}}
    <flux:navlist variant="outline">
        {{-- Dashboard --}}
        <flux:navlist.group :heading="__('Principal')" class="grid">
            <flux:navlist.item
                icon="chart-bar-square"
                :href="route('dashboard')"
                :current="request()->routeIs('dashboard')"
                wire:navigate
            >
                {{ __('Dashboard') }}
            </flux:navlist.item>

            {{-- PDV destacado --}}
            <flux:navlist.item
                icon="calculator"
                :href="route('pdv')"
                :current="request()->routeIs('pdv')"
                wire:navigate
                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700"
            >
                <div class="flex items-center justify-between w-full">
                    <span>{{ __('PDV') }}</span>
                    <flux:badge variant="light" size="sm">F12</flux:badge>
                </div>
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- Vendas e Serviços --}}
        <flux:navlist.group :heading="__('Vendas & Serviços')" class="grid">
            <flux:navlist.item
                icon="shopping-cart"
                href="#"
                :current="request()->routeIs('sales.*')"
            >
                {{ __('Vendas') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="cog-6-tooth"
                href="#"
                :current="request()->routeIs('services.*')"
            >
                {{ __('Serviços') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="calendar-days"
                href="#"
                :current="request()->routeIs('appointments.*')"
            >
                {{ __('Agendamentos') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- Gestão --}}
        <flux:navlist.group :heading="__('Gestão')" class="grid">
            <flux:navlist.item
                icon="users"
                :href="route('clients.index')"
                :current="request()->routeIs('clients.*')"
                wire:navigate
            >
                {{ __('Clientes') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="cube"
                :href="route('products.index')"
                :current="request()->routeIs('products.*')"
                wire:navigate
            >
                <div class="flex items-center justify-between w-full">
                    <span>{{ __('Produtos') }}</span>
                    {{-- Badge de estoque baixo --}}
                    @php
                        $lowStockCount = \App\Models\Inventory::where('franchise_id', auth()->user()->franchise_id)
                            ->whereRaw('quantity <= min_stock')
                            ->count();
                    @endphp
                    @if($lowStockCount > 0)
                        <flux:badge variant="danger" size="sm">{{ $lowStockCount }}</flux:badge>
                    @endif
                </div>
            </flux:navlist.item>

            <flux:navlist.item
                icon="arrows-right-left"
                href="{{ route('products.cross-franchise-search') }}"
                :current="request()->routeIs('products.cross-franchise-search')"
                wire:navigate
            >
                {{ __('Buscar nas Franquias') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- Financeiro --}}
        <flux:navlist.group :heading="__('Financeiro')" class="grid">
            <flux:navlist.item
                icon="banknotes"
                :href="route('financial.index')"
                :current="request()->routeIs('financial.*')"
                wire:navigate
            >
                {{ __('Financeiro') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="document-chart-bar"
                :href="route('reports.sales')"
                :current="request()->routeIs('reports.*')"
                wire:navigate
            >
                {{ __('Relatórios') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- Admin (apenas para super admin e franchise admin) --}}
        @if(in_array(auth()->user()->role, ['super_admin', 'franchise_admin']))
            <flux:navlist.group :heading="__('Administração')" class="grid">
                @if(auth()->user()->role === 'super_admin')
                    <flux:navlist.item
                        icon="building-office"
                        href="#"
                        :current="request()->routeIs('franchises.*')"
                    >
                        {{ __('Franquias') }}
                    </flux:navlist.item>
                @endif

                <flux:navlist.item
                    icon="user-group"
                    href="#"
                    :current="request()->routeIs('users.*')"
                >
                    {{ __('Usuários') }}
                </flux:navlist.item>

                <flux:navlist.item
                    icon="cog-8-tooth"
                    href="#"
                    :current="request()->routeIs('settings.*')"
                >
                    {{ __('Configurações') }}
                </flux:navlist.item>
            </flux:navlist.group>
        @endif
    </flux:navlist>

    <flux:spacer />

    {{-- Ações Rápidas --}}
    <div class="p-3 space-y-2">
        <flux:button
            :href="route('pdv')"
            variant="primary"
            size="sm"
            class="w-full"
            icon="calculator"
            wire:navigate
        >
            Novo Atendimento
        </flux:button>

        <flux:button
            :href="route('clients.create')"
            variant="ghost"
            size="sm"
            class="w-full"
            icon="user-plus"
            wire:navigate
        >
            Novo Cliente
        </flux:button>
    </div>

    {{-- Status da Conexão --}}
    <div class="p-3 border-t border-zinc-200 dark:border-zinc-700">
        <div x-data="{ online: navigator.onLine }"
             x-init="
                        window.addEventListener('online', () => online = true);
                        window.addEventListener('offline', () => online = false);
                     ">
            <div class="flex items-center text-xs" :class="online ? 'text-green-600' : 'text-red-600'">
                <div class="w-2 h-2 rounded-full mr-2" :class="online ? 'bg-green-500' : 'bg-red-500'"></div>
                <span x-text="online ? 'Online' : 'Offline'"></span>
            </div>
        </div>
    </div>

    {{-- Menu do Usuário (Desktop) --}}
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[240px]">
            {{-- Info do usuário --}}
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-blue-600 text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</span>
                            <span class="truncate text-xs text-blue-600">{{ __(ucfirst(auth()->user()->role)) }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            {{-- Links rápidos --}}
            <flux:menu.radio.group>
                <flux:menu.item icon="user" href="#">
                    {{ __('Meu Perfil') }}
                </flux:menu.item>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                    {{ __('Configurações') }}
                </flux:menu.item>
                <flux:menu.item icon="question-mark-circle" href="#">
                    {{ __('Ajuda') }}
                </flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-red-600">
                    {{ __('Sair') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

{{-- Header Mobile --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    {{-- Logo Mobile --}}
    <div class="flex items-center">
        <div class="flex aspect-square size-6 items-center justify-center rounded-md bg-blue-600 text-white mr-2">
            <svg class="size-4 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <span class="font-semibold text-sm">WashPro</span>
    </div>

    <flux:spacer />

    {{-- Menu Mobile --}}
    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
        />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-blue-600 text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Configurações') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Sair') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

{{-- Scripts globais --}}
<script>
    // Service Worker para funcionalidade offline
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(console.error);
    }

    // Atalhos globais
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K = Busca rápida
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            // Implementar busca rápida
        }

        // Alt + P = PDV
        if (e.altKey && e.key === 'p') {
            e.preventDefault();
            window.location.href = '{{ route("pdv") }}';
        }

        // Alt + D = Dashboard
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            window.location.href = '{{ route("dashboard") }}';
        }
    });
</script>

@fluxScripts
</body>
</html>
