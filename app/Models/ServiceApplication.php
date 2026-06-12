<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_no',
        'person_id',
        'service_id',
        'department_id',
        'assigned_officer_id',
        'status_id',
        'submitted_by',
        'priority',
        'subject',
        'description',
        'required_documents',
        'total_fee',
        'due_date',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
        'rejection_reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'total_fee' => 'decimal:2',
            'required_documents' => 'array',
            'due_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function status()
    {
        return $this->belongsTo(ApplicationStatus::class, 'status_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id')->oldest('changed_at');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'application_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'application_id');
    }

    public function notes()
    {
        return $this->hasMany(ApplicationNote::class, 'application_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
