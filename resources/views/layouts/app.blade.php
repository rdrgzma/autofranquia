<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP Franquias')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <div class="w-64 bg-gray-800 text-white p-4">
        <h2 class="text-xl font-bold mb-4">ERP Franquias</h2>
        <ul>
            <li class="mb-2"><a href="{{ route('pdv') }}" class="hover:underline">PDV</a></li>
            <li class="mb-2"><a href="{{ route('products.index') }}" class="hover:underline">Produtos</a></li>
            <li class="mb-2"><a href="{{ route('clients.index') }}" class="hover:underline">Clientes</a></li>
            <li class="mb-2"><a href="#" class="hover:underline">Vendas</a></li>
            <li class="mb-2"><a href="{{ route('financial.index') }}" class="hover:underline">Financeiro</a></li>
            @if(auth()->user()->role === 'super_admin')
                <li class="mb-2"><a href="#" class="hover:underline">Admin</a></li>
            @endif
        </ul>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-xl">@yield('header')</h1>
                <div>
                    Olá, {{ auth()->user()->name }}
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="ml-4 text-red-500">Sair</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-4">
            @yield('content')
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
