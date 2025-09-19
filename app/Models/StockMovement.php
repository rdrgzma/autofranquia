<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'franchise_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
        'notes',
        'user_id',
        'reference_franchise_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'previous_quantity' => 'decimal:3',
        'new_quantity' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referenceFranchise()
    {
        return $this->belongsTo(Franchise::class, 'reference_franchise_id');
    }
}
