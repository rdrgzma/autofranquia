<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
use HasFactory;

protected $fillable = [
'name',
'description',
'price',
'duration_minutes',
'category',
'is_active',
'franchise_id', // Se específico por franquia
];

protected $casts = [
'price' => 'decimal:2',
'duration_minutes' => 'integer',
'is_active' => 'boolean',
];

public function franchise()
{
return $this->belongsTo(Franchise::class);
}
}
