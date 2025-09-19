<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id',
        'client_id',
        'service_id',
        'scheduled_at',
        'estimated_duration',
        'status',
        'notes',
        'created_by',
        'vehicle_info',
        'bay_number', // Número da baia/posto
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'estimated_duration' => 'integer',
        'vehicle_info' => 'array',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEstimatedEndTimeAttribute()
    {
        return $this->scheduled_at->addMinutes($this->estimated_duration);
    }
}
