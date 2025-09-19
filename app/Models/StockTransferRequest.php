<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'from_franchise_id',
        'to_franchise_id',
        'requested_quantity',
        'approved_quantity',
        'status',
        'requested_by',
        'approved_by',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'requested_quantity' => 'integer',
        'approved_quantity' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromFranchise()
    {
        return $this->belongsTo(Franchise::class, 'from_franchise_id');
    }

    public function toFranchise()
    {
        return $this->belongsTo(Franchise::class, 'to_franchise_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
