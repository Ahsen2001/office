<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'email', 'location', 'department_officer_id', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
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

    public function officer()
    {
        return $this->belongsTo(User::class, 'department_officer_id');
    }
}
