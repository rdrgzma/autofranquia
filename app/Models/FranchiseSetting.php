<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseSetting extends Model
{
    protected $fillable = ['franchise_id', 'key', 'value', 'type', 'description'];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function getTypedValueAttribute()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value
        };
    }
}
