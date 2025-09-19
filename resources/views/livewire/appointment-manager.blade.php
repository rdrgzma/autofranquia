<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <flux:heading size="xl">Agendamentos</flux:heading>
            <flux:subheading>Gestão completa dos horários de atendimento</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            {{-- View toggles --}}
            <div class="flex bg-zinc-100 dark:bg-zinc-800 rounded-lg p-1">
                <flux:button
                    wire:click="$set('currentView', 'calendar')"
                    variant="{{ $currentView === 'calendar' ? 'filled' : 'ghost' }}"
                    size="sm"
                    icon="calendar-days"
                >
                    Calendário
                </flux:button>
                <flux:button
                    wire:click="$set('currentView', 'list')"
                    variant="{{ $currentView === 'list' ? 'filled' : 'ghost' }}"
                    size="sm"
                    icon="list-bullet"
                >
                    Lista
                </flux:button>
            </div>

            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Novo Agendamento
            </flux:button>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Hoje</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['today_appointments'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <flux:icon.calendar-days class="w-6 h-6 text-blue-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Pendentes</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_appointments'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                    <flux:icon.clock class="w-6 h-6 text-orange-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Em Andamento</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <flux:icon.play class="w-6 h-6 text-green-600" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-6 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">Concluídos Hoje</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['completed_today'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <flux:icon.check-circle class="w-6 h-6 text-purple-600" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm p-4 border border-zinc-200 dark:border-zinc-700">
        <div class="flex flex-col md:flex-row gap-4">
            @if($currentView === 'calendar')
                {{-- Navegação de data para calendário --}}
                <div class="flex items-center space-x-2">
                    <flux:button wire:click="prevWeek" variant="ghost" size="sm" icon="chevron-left">
                    </flux:button>
                    <flux:input
                        wire:model.live="selectedDate"
                        type="date"
                        class="w-40"
                    />
                    <flux:button wire:click="nextWeek" variant="ghost" size="sm" icon="chevron-right">
                    </flux:button>
                </div>
            @endif

            <div class="flex items-center space-x-2">
                <flux:select wire:model.live="dateFilter" class="w-40">
                    <option value="today">Hoje</option>
                    <option value="tomorrow">Amanhã</option>
                    <option value="week">Esta Semana</option>
                </flux:select>

                <flux:select wire:model.live="statusFilter" class="w-40">
                    <option value="all">Todos os Status</option>
                    <option value="scheduled">Agendado</option>
                    <option value="in_progress">Em Andamento</option>
                    <option value="completed">Concluído</option>
                    <option value="cancelled">Cancelado</option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal --}}
    @if($currentView === 'calendar')
        {{-- Visualização em Calendário --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            {{-- Header do calendário --}}
            <div class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <div class="grid grid-cols-8 gap-4">
                    <div class="font-medium text-zinc-600 dark:text-zinc-400">Horário</div>
                    @foreach($weekDays as $day)
                        <div class="text-center">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $day['day'] }}</div>
                            <div class="text-sm text-zinc-500 {{ $day['isToday'] ? 'text-blue-600 font-bold' : '' }}">
                                {{ $day['number'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Grid do calendário --}}
            <div class="max-h-96 overflow-y-auto">
                @foreach($timeSlots as $time)
                    <div class="grid grid-cols-8 gap-4 border-b border-zinc-100 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        {{-- Horário --}}
                        <div class="p-3 text-sm font-medium text-zinc-600 dark:text-zinc-400 border-r border-zinc-200 dark:border-zinc-700">
                            {{ $time }}
                        </div>

                        {{-- Slots por dia --}}
                        @foreach($weekDays as $day)
                            <div class="p-2">
                                @php
                                    $dayAppointments = collect($appointments)->filter(function($apt) use ($day, $time) {
                                        $aptDate = \Carbon\Carbon::parse($apt['scheduled_at']);
                                        return $aptDate->format('Y-m-d') === $day['date'] &&
                                               $aptDate->format('H:i') === $time;
                                    });
                                @endphp

                                @if($dayAppointments->count() > 0)
                                    @foreach($dayAppointments as $appointment)
                                        <div class="mb-1 p-2 rounded-lg text-xs cursor-pointer {{ $this->getAppointmentBadgeClass($appointment['status']) }}"
                                             wire:click="openDetailsModal({{ $appointment['id'] }})">
                                            <div class="font-medium">{{ $appointment['client']['name'] ?? 'Cliente' }}</div>
                                            <div>{{ $appointment['service']['name'] ?? 'Serviço' }}</div>
                                            <div>Baia {{ $appointment['bay_number'] }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Slot vazio - clicável para criar agendamento --}}
                                    @if(!$day['isPast'])
                                        @foreach($bayNumbers as $bay)
                                            <div class="mb-1 p-1 border border-dashed border-zinc-300 dark:border-zinc-600 rounded opacity-50 hover:opacity-100 cursor-pointer text-xs text-center"
                                                 wire:click="openCreateModal('{{ $day['date'] }}', '{{ $time }}', '{{ $bay }}')"
                                                 title="Baia {{ $bay }} - Clique para agendar">
                                                B{{ $bay }}
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Visualização em Lista --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="text-left p-4 font-medium">Cliente</th>
                        <th class="text-left p-4 font-medium">Serviço</th>
                        <th class="text-left p-4 font-medium">Data/Hora</th>
                        <th class="text-center p-4 font-medium">Baia</th>
                        <th class="text-center p-4 font-medium">Status</th>
                        <th class="text-center p-4 font-medium">Ações</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($appointments as $appointment)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="p-4">
                                <div>
                                    <div class="font-medium">{{ $appointment['client']['name'] ?? 'N/A' }}</div>
                                    @if(isset($appointment['vehicle_info']['plate']))
                                        <div class="text-sm text-zinc-500">{{ $appointment['vehicle_info']['plate'] }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <div>
                                    <div class="font-medium">{{ $appointment['service']['name'] ?? 'N/A' }}</div>
                                    <div class="text-sm text-zinc-500">{{ $appointment['estimated_duration'] }} min</div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div>
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($appointment['scheduled_at'])->format('d/m/Y') }}</div>
                                    <div class="text-sm text-zinc-500">{{ \Carbon\Carbon::parse($appointment['scheduled_at'])->format('H:i') }}</div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <flux:badge variant="subtle">Baia {{ $appointment['bay_number'] }}</flux:badge>
                            </td>
                            <td class="p-4 text-center">
                                <flux:badge variant="{{ $this->getStatusVariant($appointment['status']) }}">
                                    {{ $this->getStatusLabel($appointment['status']) }}
                                </flux:badge>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-1">
                                    {{-- Ações baseadas no status --}}
                                    @if($appointment['status'] === 'scheduled')
                                        <flux:button
                                            wire:click="updateStatus({{ $appointment['id'] }}, 'in_progress')"
                                            variant="ghost"
                                            size="sm"
                                            icon="play"
                                            title="Iniciar atendimento"
                                        >
                                        </flux:button>
                                    @endif

                                    @if($appointment['status'] === 'in_progress')
                                        <flux:button
                                            wire:click="updateStatus({{ $appointment['id'] }}, 'completed')"
                                            variant="ghost"
                                            size="sm"
                                            icon="check"
                                            title="Concluir atendimento"
                                        >
                                        </flux:button>
                                    @endif

                                    <flux:button
                                        wire:click="openEditModal({{ $appointment['id'] }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        title="Editar"
                                    >
                                    </flux:button>

                                    <flux:button
                                        wire:click="openDetailsModal({{ $appointment['id'] }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        title="Detalhes"
                                    >
                                    </flux:button>

                                    @if($appointment['status'] === 'scheduled')
                                        <flux:button
                                            wire:click="updateStatus({{ $appointment['id'] }}, 'cancelled')"
                                            variant="ghost"
                                            size="sm"
                                            icon="x-mark"
                                            title="Cancelar"
                                            onclick="return confirm('Cancelar este agendamento?')"
                                        >
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <flux:icon.calendar-days class="w-16 h-16 mx-auto text-zinc-400 mb-4" />
                                <flux:heading size="lg" class="text-zinc-500 mb-2">Nenhum agendamento encontrado</flux:heading>
                                <flux:subheading class="text-zinc-400 mb-4">
                                    Ajuste os filtros ou crie um novo agendamento
                                </flux:subheading>
                                <flux:button wire:click="openCreateModal" variant="primary">
                                    Novo Agendamento
                                </flux:button>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Criar/Editar Agendamento --}}
    <flux:modal name="create-appointment" :show="$showCreateModal || $showEditModal" class="max-w-2xl">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">
                {{ $showEditModal ? 'Editar' : 'Novo' }} Agendamento
            </flux:heading>

            <div class="space-y-6">
                {{-- Cliente --}}
                <div>
                    <flux:label>Cliente</flux:label>
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
                    @elseif($clientSearch && strlen($clientSearch) >= 2)
                        <div class="mt-2">
                            <flux:button wire:click="createQuickClient" variant="ghost" size="sm" class="w-full">
                                + Criar cliente "{{ $clientSearch }}"
                            </flux:button>
                        </div>
                    @endif

                    @if($clientId)
                        <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm">
                            ✓ Cliente selecionado
                        </div>
                    @endif
                </div>

                {{-- Informações do Veículo --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Marca</flux:label>
                        <flux:input wire:model="vehicleInfo.brand" placeholder="ex: Toyota" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Modelo</flux:label>
                        <flux:input wire:model="vehicleInfo.model" placeholder="ex: Corolla" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Placa</flux:label>
                        <flux:input wire:model="vehicleInfo.plate" placeholder="ABC-1234" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Cor</flux:label>
                        <flux:input wire:model="vehicleInfo.color" placeholder="ex: Branco" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Ano</flux:label>
                        <flux:input wire:model="vehicleInfo.year" type="number" placeholder="2020" />
                    </flux:field>
                </div>

                {{-- Serviço --}}
                <flux:field>
                    <flux:label>Serviço</flux:label>
                    <flux:select wire:model="serviceId">
                        <option value="">Selecione o serviço</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} - R$ {{ number_format($service->price, 2, ',', '.') }} ({{ $service->duration_minutes }}min)
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>

                {{-- Data e Hora --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Data</flux:label>
                        <flux:input wire:model="scheduledDate" type="date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Horário</flux:label>
                        <flux:select wire:model="scheduledTime">
                            <option value="">Selecione o horário</option>
                            @foreach($timeSlots as $slot)
                                <option value="{{ $slot }}">{{ $slot }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Baia</flux:label>
                        <flux:select wire:model="bayNumber">
                            <option value="">Selecione a baia</option>
                            @foreach($bayNumbers as $bay)
                                <option value="{{ $bay }}">Baia {{ $bay }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                {{-- Observações --}}
                <flux:field>
                    <flux:label>Observações</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Observações especiais..." rows="3" />
                </flux:field>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <flux:button wire:click="$set('showCreateModal', false); $set('showEditModal', false)" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="saveAppointment" variant="primary">
                    {{ $showEditModal ? 'Atualizar' : 'Criar' }} Agendamento
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Detalhes --}}
    <flux:modal name="appointment-details" :show="$showDetailsModal">
        <div class="p-6">
            @if($appointmentId)
                @php $appointment = collect($appointments)->firstWhere('id', $appointmentId); @endphp
                @if($appointment)
                    <flux:heading size="lg" class="mb-4">Detalhes do Agendamento</flux:heading>

                    <div class="space-y-4">
                        {{-- Status e horário --}}
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <div>
                                <h4 class="font-medium">{{ \Carbon\Carbon::parse($appointment['scheduled_at'])->format('d/m/Y H:i') }}</h4>
                                <p class="text-sm text-zinc-600">Baia {{ $appointment['bay_number'] }}</p>
                            </div>
                            <flux:badge variant="{{ $this->getStatusVariant($appointment['status']) }}">
                                {{ $this->getStatusLabel($appointment['status']) }}
                            </flux:badge>
                        </div>

                        {{-- Cliente --}}
                        <div>
                            <h5 class="font-medium mb-2">Cliente</h5>
                            <p>{{ $appointment['client']['name'] ?? 'N/A' }}</p>
                            @if(isset($appointment['client']['phone']))
                                <p class="text-sm text-zinc-600">{{ $appointment['client']['phone'] }}</p>
                            @endif
                        </div>

                        {{-- Veículo --}}
                        @if($appointment['vehicle_info'])
                            <div>
                                <h5 class="font-medium mb-2">Veículo</h5>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    @if(isset($appointment['vehicle_info']['brand']))
                                        <p><span class="text-zinc-600">Marca:</span> {{ $appointment['vehicle_info']['brand'] }}</p>
                                    @endif
                                    @if(isset($appointment['vehicle_info']['model']))
                                        <p><span class="text-zinc-600">Modelo:</span> {{ $appointment['vehicle_info']['model'] }}</p>
                                    @endif
                                    @if(isset($appointment['vehicle_info']['plate']))
                                        <p><span class="text-zinc-600">Placa:</span> {{ $appointment['vehicle_info']['plate'] }}</p>
                                    @endif
                                    @if(isset($appointment['vehicle_info']['color']))
                                        <p><span class="text-zinc-600">Cor:</span> {{ $appointment['vehicle_info']['color'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Serviço --}}
                        <div>
                            <h5 class="font-medium mb-2">Serviço</h5>
                            <p>{{ $appointment['service']['name'] ?? 'N/A' }}</p>
                            <p class="text-sm text-zinc-600">Duração: {{ $appointment['estimated_duration'] }} minutos</p>
                        </div>

                        {{-- Observações --}}
                        @if($appointment['notes'])
                            <div>
                                <h5 class="font-medium mb-2">Observações</h5>
                                <p class="text-sm">{{ $appointment['notes'] }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <flux:button wire:click="$set('showDetailsModal', false)" variant="ghost">
                            Fechar
                        </flux:button>
                        <flux:button wire:click="openEditModal({{ $appointment['id'] }})" variant="primary">
                            Editar
                        </flux:button>
                    </div>
                @endif
            @endif
        </div>
    </flux:modal>
</div>

{{-- Helper methods - adicionar ao componente --}}
@script
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('appointmentCreated', () => {
            // Refresh calendar view
            window.location.reload();
        });
    });
</script>
@endscript

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
