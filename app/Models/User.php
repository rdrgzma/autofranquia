<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'franchise_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function financialTransactionsCreated()
    {
        return $this->hasMany(FinancialTransaction::class, 'created_by');
    }

    /** Helpers */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isFranchiseAdmin(): bool
    {
        return $this->role === 'franchise_admin';
    }
    /**
     * Retorna as iniciais do nome do usuário (ex: João Silva -> JS).
     *
     * @return string
     */
    public function initials(): string
    {
        $name = trim($this->name ?? '');

        if ($name === '') {
            return '?';
        }

        // Pega as primeiras letras das duas primeiras palavras
        $parts = array_filter(preg_split('/\s+/', $name));
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            // mb_substr para suportar acentuação/UTF-8
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    }
}
