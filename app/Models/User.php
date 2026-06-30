<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'activation_token', 'activation_expires_at'])]
#[Hidden(['password', 'remember_token', 'activation_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_CLIENTE = 'cliente';
    const ROLE_OPERADOR = 'operador';

    const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CLIENTE,
        self::ROLE_OPERADOR,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activation_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCliente(): bool
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function isOperador(): bool
    {
        return $this->role === self::ROLE_OPERADOR;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
