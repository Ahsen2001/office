<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'department_id',
        'code',
        'name',
        'description',
        'required_documents',
        'fee_amount',
        'processing_time_days',
        'estimated_days',
        'requires_appointment',
        'requires_payment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
            'required_documents' => 'array',
            'requires_appointment' => 'boolean',
            'requires_payment' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function applications()
    {
        return $this->hasMany(ServiceApplication::class);
    }
}
