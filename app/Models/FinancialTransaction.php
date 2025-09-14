<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFranchiseScope;

class FinancialTransaction extends Model
{
    use HasFactory, HasFranchiseScope;

    protected $fillable = [
        'franchise_id',
        'type',
        'value',
        'description',
        'date',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'date' => 'date',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
