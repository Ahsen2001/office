<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'branch_id',
        'created_by',
        'name',
        'email',
        'phone',
        'designation',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function assignedApplications()
    {
        return $this->hasMany(ServiceApplication::class, 'assigned_officer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function hasRole(string ...$roles): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function primaryDashboardRoute(): string
    {
        return match (true) {
            $this->hasRole('admin') => 'admin.dashboard',
            $this->hasRole('management') => 'management.dashboard',
            $this->hasRole('reception') => 'reception.dashboard',
            $this->hasRole('branch_head') => 'branch-head.dashboard',
            $this->hasRole('branch_staff') => 'branch-staff.dashboard',
            default => 'dashboard',
        };
    }

    public function isBranchRestricted(): bool
    {
        return $this->hasRole('branch_head', 'branch_staff');
    }
}
