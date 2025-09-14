<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'price',
        'default_image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Inventário em uma franquia específica (retorna relação Query)
     */
    public function inventoryForFranchise($franchiseId)
    {
        return $this->inventories()->where('franchise_id', $franchiseId);
    }
}
