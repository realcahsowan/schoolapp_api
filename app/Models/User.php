<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'student_id',
        'guardian_id',
        'avatar_url', // tambahkan ini
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_user');
    }

    // FilamentUser implementation
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return in_array($this->role, ['admin', 'superuser']);
        }

        if ($panel->getId() === 'tataUsaha') {
            return $this->employee
                && $this->employee->positions
                    ->contains('nama', 'tata-usaha');
        }

        if ($panel->getId() === 'adminTahfidz') {
            return $this->employee
                && $this->employee->positions
                    ->contains('nama', 'Admin-tahfidz');
        }

        return false;
    }

    // HasTenants implementation
    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        // Implement your logic here, for now allow all
        return true;
    }

    /**
     * @return array<\Illuminate\Database\Eloquent\Model> | \Illuminate\Support\Collection
     */
    public function getTenants(\Filament\Panel $panel): array|\Illuminate\Support\Collection
    {
        return $this->schools;
    }
}
