<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'department_id',
        'branch_id',
        'code',
        'name',
        'description',
        'required_documents',
        'fee_amount',
        'processing_time_days',
        'estimated_days',
        'requires_appointment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'required_documents' => 'array',
            'fee_amount' => 'decimal:2',
            'requires_appointment' => 'boolean',
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

    public function applications()
    {
        return $this->hasMany(ServiceApplication::class);
    }
}
