<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** Consulta y prepara la nómina; no administra usuarios. */
    public const ROLE_CONTADOR = 'contador';

    public const ROLE_ADMINISTRADOR = 'administrador';

    /** Dueño del proyecto y del hosting. */
    public const ROLE_GERENTE = 'gerente';

    /** Desarrollador del sistema. */
    public const ROLE_SUPERADMIN = 'superadmin';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_GERENTE,
            self::ROLE_ADMINISTRADOR,
            self::ROLE_CONTADOR,
        ];
    }

    /**
     * Roles con acceso a todos los módulos y a la gestión de usuarios.
     *
     * @return list<string>
     */
    public static function rolesAdministrativos(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_GERENTE,
            self::ROLE_ADMINISTRADOR,
        ];
    }

    /**
     * Roles con control total (gerente y desarrollador): son los únicos que crean,
     * modifican o eliminan cuentas con estos mismos roles.
     *
     * @return list<string>
     */
    public static function rolesControlTotal(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_GERENTE,
        ];
    }

    /**
     * Roles que el usuario puede asignar al crear o editar cuentas.
     *
     * @return list<string>
     */
    public function rolesAsignables(): array
    {
        return $this->tieneControlTotal()
            ? self::roles()
            : array_values(array_diff(self::roles(), self::rolesControlTotal()));
    }

    public static function etiquetaRol(string $role): string
    {
        return match (strtolower($role)) {
            self::ROLE_SUPERADMIN => 'Super administrador',
            self::ROLE_GERENTE => 'Gerente',
            self::ROLE_ADMINISTRADOR => 'Administrador',
            self::ROLE_CONTADOR => 'Contador',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    public function hasRole(string $role): bool
    {
        return strcasecmp($this->role, $role) === 0;
    }

    /**
     * @param  list<string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(strtolower($this->role), array_map('strtolower', $roles), true);
    }

    /**
     * Acceso de administrador: también lo tienen el gerente y el super administrador.
     */
    public function isAdministrador(): bool
    {
        return $this->hasAnyRole(self::rolesAdministrativos());
    }

    public function tieneControlTotal(): bool
    {
        return $this->hasAnyRole(self::rolesControlTotal());
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
