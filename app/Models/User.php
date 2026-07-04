<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'nombres', 'apellido_paterno', 'apellido_materno', 'telefono', 'estatus', 'fecha_baja', 'activation_token', 'activation_expires_at'])]
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

    const ESTATUS_ACTIVO = 'activo';
    const ESTATUS_INACTIVO = 'inactivo';
    const ESTATUS_SUSPENDIDO = 'suspendido';
    const ESTATUS_DADO_DE_BAJA = 'dado_de_baja';

    const ESTATUS = [
        self::ESTATUS_ACTIVO,
        self::ESTATUS_INACTIVO,
        self::ESTATUS_SUSPENDIDO,
        self::ESTATUS_DADO_DE_BAJA,
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
            'fecha_baja' => 'date',
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

    /**
     * Generate the display name from the person's names and surnames.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($user) {
            $parts = array_filter([
                trim($user->nombres ?? ''),
                trim($user->apellido_paterno ?? ''),
                trim($user->apellido_materno ?? ''),
            ]);

            if (!empty($parts)) {
                $user->name = implode(' ', $parts);
            }
        });
    }
}
