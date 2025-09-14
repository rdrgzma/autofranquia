<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFranchiseScope;

class Sale extends Model
{
    use HasFactory, HasFranchiseScope;

    protected $fillable = [
        'franchise_id',
        'user_id',
        'client_id',
        'total',
        'payment_method',
        'discount',
        'extra',
        'date',
        'receipt_number',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'extra' => 'decimal:2',
        'date' => 'date',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        if ($start) $query->where('date', '>=', $start);
        if ($end) $query->where('date', '<=', $end);
        return $query;
    }
}
