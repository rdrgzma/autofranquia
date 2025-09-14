<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFranchiseScope;

class Client extends Model
{
    use HasFactory, HasFranchiseScope;

    protected $fillable = [
        'name',
        'document',
        'document_type',
        'phone',
        'email',
        'vehicle',
        'address',
        'franchise_id',
    ];

    protected $casts = [
        'address' => 'array',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
