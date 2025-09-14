<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFranchiseScope;

class Inventory extends Model
{
    use HasFactory, HasFranchiseScope;

    protected $fillable = [
        'product_id',
        'franchise_id',
        'quantity',
        'location',
        'min_stock',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }
}
