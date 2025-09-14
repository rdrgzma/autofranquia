<div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-4">
        <input wire:model="start" type="date" class="p-2 border rounded" />
        <input wire:model="end" type="date" class="p-2 border rounded" />
        @if(auth()->user()->role === 'super_admin')
            <select wire:model="franchise_id" class="p-2 border rounded">
                <option value="">Todas as Franquias</option>
                @foreach($franchises as $f)
                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                @endforeach
            </select>
        @endif
        <div>
            <button wire:click="generate" class="bg-blue-600 text-white px-3 py-2 rounded">Gerar</button>
        </div>
    </div>

    <div class="mb-4">
        <strong>Total:</strong> R$ {{ number_format($total,2,',','.') }}
    </div>

    <table class="min-w-full border">
        <thead><tr class="bg-gray-100">
            <th class="p-2 border">Data</th>
            <th class="p-2 border">Franquia</th>
            <th class="p-2 border">Usuário</th>
            <th class="p-2 border">Total</th>
            <th class="p-2 border">Pagamento</th>
        </tr></thead>
        <tbody>
        @foreach($sales as $s)
            <tr>
                <td class="p-2 border">{{ $s->date }}</td>
                <td class="p-2 border">{{ optional($s->franchise)->name }}</td>
                <td class="p-2 border">{{ optional($s->user)->name }}</td>
                <td class="p-2 border">R$ {{ number_format($s->total,2,',','.') }}</td>
                <td class="p-2 border">{{ $s->payment_method }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
