<?php

namespace App\Livewire\Appointments;

use Livewire\Component;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppointmentManager extends Component
{
    use AuthorizesRequests;

    public $currentView = 'calendar'; // calendar, list, create
    public $selectedDate;
    public $selectedWeek;
    public $appointments = [];
    public $workingHours = ['08:00', '18:00'];
    public $bayNumbers = ['1', '2', '3', '4'];

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDetailsModal = false;

    // Form data
    public $appointmentId = null;
    public $clientId = null;
    public $clientSearch = '';
    public $serviceId = null;
    public $scheduledDate = '';
    public $scheduledTime = '';
    public $bayNumber = '';
    public $notes = '';
    public $vehicleInfo = [
        'brand' => '',
        'model' => '',
        'plate' => '',
        'color' => '',
        'year' => '',
    ];

    // Filters
    public $statusFilter = 'all';
    public $dateFilter = 'today';

    protected $listeners = [
        'appointmentCreated' => 'refreshAppointments',
        'appointmentUpdated' => 'refreshAppointments',
        'selectTimeSlot' => 'handleTimeSlotSelection',
    ];

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedWeek = now()->startOfWeek();
        $this->scheduledDate = now()->format('Y-m-d');
        $this->loadAppointments();
    }

    public function updatedSelectedDate()
    {
        $this->loadAppointments();
    }

    public function updatedCurrentView()
    {
        $this->loadAppointments();
    }

    public function updatedStatusFilter()
    {
        $this->loadAppointments();
    }

    public function updatedDateFilter()
    {
        switch ($this->dateFilter) {
            case 'today':
                $this->selectedDate = now()->format('Y-m-d');
                break;
            case 'tomorrow':
                $this->selectedDate = now()->addDay()->format('Y-m-d');
                break;
            case 'week':
                $this->selectedDate = now()->startOfWeek()->format('Y-m-d');
                break;
        }
        $this->loadAppointments();
    }

    public function loadAppointments()
    {
        $query = Appointment::with(['client', 'service', 'createdBy'])
            ->where('franchise_id', auth()->user()->franchise_id);

        if ($this->currentView === 'calendar') {
            $query->whereDate('scheduled_at', $this->selectedDate);
        } elseif ($this->dateFilter === 'week') {
            $startOfWeek = Carbon::parse($this->selectedDate)->startOfWeek();
            $endOfWeek = Carbon::parse($this->selectedDate)->endOfWeek();
            $query->whereBetween('scheduled_at', [$startOfWeek, $endOfWeek]);
        } elseif ($this->dateFilter === 'today') {
            $query->whereDate('scheduled_at', now());
        } elseif ($this->dateFilter === 'tomorrow') {
            $query->whereDate('scheduled_at', now()->addDay());
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->appointments = $query->orderBy('scheduled_at')->get()->toArray();
    }

    // Modal management
    public function openCreateModal($date = null, $time = null, $bay = null)
    {
        $this->resetForm();
        $this->scheduledDate = $date ?? $this->selectedDate;
        $this->scheduledTime = $time ?? '';
        $this->bayNumber = $bay ?? '';
        $this->showCreateModal = true;
    }

    public function openEditModal($appointmentId)
    {
        $appointment = Appointment::with('client')->find($appointmentId);
        if (!$appointment) return;

        $this->appointmentId = $appointment->id;
        $this->clientId = $appointment->client_id;
        $this->clientSearch = $appointment->client->name;
        $this->serviceId = $appointment->service_id;
        $this->scheduledDate = $appointment->scheduled_at->format('Y-m-d');
        $this->scheduledTime = $appointment->scheduled_at->format('H:i');
        $this->bayNumber = $appointment->bay_number;
        $this->notes = $appointment->notes;
        $this->vehicleInfo = $appointment->vehicle_info ?? [];

        $this->showEditModal = true;
    }

    public function openDetailsModal($appointmentId)
    {
        $this->appointmentId = $appointmentId;
        $this->showDetailsModal = true;
    }

    public function resetForm()
    {
        $this->appointmentId = null;
        $this->clientId = null;
        $this->clientSearch = '';
        $this->serviceId = null;
        $this->scheduledTime = '';
        $this->bayNumber = '';
        $this->notes = '';
        $this->vehicleInfo = [
            'brand' => '',
            'model' => '',
            'plate' => '',
            'color' => '',
            'year' => '',
        ];
    }

    public function selectClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->clientId = $client->id;
            $this->clientSearch = $client->name;

            // Auto-fill vehicle info if available
            if ($client->vehicle) {
                $vehicleData = is_array($client->vehicle) ? $client->vehicle : json_decode($client->vehicle, true);
                if ($vehicleData) {
                    $this->vehicleInfo = array_merge($this->vehicleInfo, $vehicleData);
                }
            }
        }
    }

    public function createQuickClient()
    {
        if (!$this->clientSearch) return;

        $client = Client::create([
            'name' => $this->clientSearch,
            'franchise_id' => auth()->user()->franchise_id,
        ]);

        $this->selectClient($client->id);
    }

    public function saveAppointment()
    {
        $this->validate([
            'clientId' => 'required|exists:clients,id',
            'serviceId' => 'required|exists:services,id',
            'scheduledDate' => 'required|date|after_or_equal:today',
            'scheduledTime' => 'required',
            'bayNumber' => 'required|string',
        ]);

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', $this->scheduledDate . ' ' . $this->scheduledTime);

        // Check for conflicts
        $conflict = Appointment::where('franchise_id', auth()->user()->franchise_id)
            ->where('bay_number', $this->bayNumber)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($scheduledAt) {
                $service = Service::find($this->serviceId);
                $endTime = $scheduledAt->copy()->addMinutes($service->duration_minutes);

                $q->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                    ->orWhere(function($sq) use ($scheduledAt, $endTime) {
                        $sq->where('scheduled_at', '<=', $scheduledAt)
                            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
                    });
            });

        if ($this->appointmentId) {
            $conflict->where('id', '!=', $this->appointmentId);
        }

        if ($conflict->exists()) {
            session()->flash('error', 'Horário já ocupado nesta baia!');
            return;
        }

        $service = Service::find($this->serviceId);

        $data = [
            'franchise_id' => auth()->user()->franchise_id,
            'client_id' => $this->clientId,
            'service_id' => $this->serviceId,
            'scheduled_at' => $scheduledAt,
            'estimated_duration' => $service->duration_minutes,
            'bay_number' => $this->bayNumber,
            'notes' => $this->notes,
            'vehicle_info' => array_filter($this->vehicleInfo),
            'created_by' => auth()->id(),
        ];

        if ($this->appointmentId) {
            Appointment::find($this->appointmentId)->update($data);
            $message = 'Agendamento atualizado com sucesso!';
        } else {
            Appointment::create($data);
            $message = 'Agendamento criado com sucesso!';
        }

        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->refreshAppointments();
        session()->flash('success', $message);
    }

    public function updateStatus($appointmentId, $status)
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment) {
            $appointment->update(['status' => $status]);
            $this->refreshAppointments();

            $statusLabels = [
                'in_progress' => 'em andamento',
                'completed' => 'concluído',
                'cancelled' => 'cancelado',
                'no_show' => 'não compareceu',
            ];

            session()->flash('success', 'Agendamento marcado como ' . ($statusLabels[$status] ?? $status));
        }
    }

    public function deleteAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment) {
            $appointment->delete();
            $this->refreshAppointments();
            session()->flash('success', 'Agendamento excluído com sucesso!');
        }
    }

    public function refreshAppointments()
    {
        $this->loadAppointments();
    }

    public function handleTimeSlotSelection($date, $time, $bay)
    {
        $this->openCreateModal($date, $time, $bay);
    }

    public function nextWeek()
    {
        $this->selectedWeek = $this->selectedWeek->addWeek();
        $this->selectedDate = $this->selectedWeek->format('Y-m-d');
    }

    public function prevWeek()
    {
        $this->selectedWeek = $this->selectedWeek->subWeek();
        $this->selectedDate = $this->selectedWeek->format('Y-m-d');
    }

    public function getTimeSlots()
    {
        $slots = [];
        $start = Carbon::createFromFormat('H:i', $this->workingHours[0]);
        $end = Carbon::createFromFormat('H:i', $this->workingHours[1]);

        while ($start < $end) {
            $slots[] = $start->format('H:i');
            $start->addMinutes(30);
        }

        return $slots;
    }

    public function getWeekDays()
    {
        $days = [];
        $date = $this->selectedWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'number' => $date->format('j'),
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast(),
            ];
            $date->addDay();
        }

        return $days;
    }

    public function getAppointmentBadgeClass($status)
    {
        return match($status) {
            'scheduled' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'in_progress' => 'bg-green-100 text-green-800 border border-green-200',
            'completed' => 'bg-purple-100 text-purple-800 border border-purple-200',
            'cancelled' => 'bg-red-100 text-red-800 border border-red-200',
            'no_show' => 'bg-gray-100 text-gray-800 border border-gray-200',
            default => 'bg-gray-100 text-gray-800 border border-gray-200',
        };
    }

    public function getStatusVariant($status)
    {
        return match($status) {
            'scheduled' => 'primary',
            'in_progress' => 'success',
            'completed' => 'secondary',
            'cancelled' => 'danger',
            'no_show' => 'subtle',
            default => 'subtle',
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            'scheduled' => 'Agendado',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show' => 'Não Compareceu',
            default => 'N/A',
        };
    }

    public function render()
    {
        $services = Service::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('franchise_id')
                    ->orWhere('franchise_id', auth()->user()->franchise_id);
            })
            ->orderBy('name')
            ->get();

        $clients = [];
        if ($this->clientSearch && strlen($this->clientSearch) >= 2) {
            $clients = Client::where('franchise_id', auth()->user()->franchise_id)
                ->where(function($q) {
                    $q->where('name', 'like', "%{$this->clientSearch}%")
                        ->orWhere('phone', 'like', "%{$this->clientSearch}%");
                })
                ->limit(10)
                ->get();
        }

        // Statistics
        $today = now()->toDateString();
        $stats = [
            'today_appointments' => Appointment::where('franchise_id', auth()->user()->franchise_id)
                ->whereDate('scheduled_at', $today)
                ->count(),
            'pending_appointments' => Appointment::where('franchise_id', auth()->user()->franchise_id)
                ->where('status', 'scheduled')
                ->whereDate('scheduled_at', '>=', $today)
                ->count(),
            'in_progress' => Appointment::where('franchise_id', auth()->user()->franchise_id)
                ->where('status', 'in_progress')
                ->count(),
            'completed_today' => Appointment::where('franchise_id', auth()->user()->franchise_id)
                ->where('status', 'completed')
                ->whereDate('updated_at', $today)
                ->count(),
        ];

        return view('livewire.appointments.manager', [
            'services' => $services,
            'clients' => $clients,
            'stats' => $stats,
            'timeSlots' => $this->getTimeSlots(),
            'weekDays' => $this->getWeekDays(),
        ]);
    }
}
