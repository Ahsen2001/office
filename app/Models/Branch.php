<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'branch_head_user_id',
        'phone',
        'email',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'branch_head_user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function applications()
    {
        return $this->hasMany(ServiceApplication::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin', 'management', 'reception')) {
            return $query;
        }

        return $query->whereKey($user->branch_id ?: 0);
    }
}
