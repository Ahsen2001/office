<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['application_id', 'department_id', 'branch_id', 'assigned_officer_id', 'from_status_id', 'to_status_id', 'changed_by', 'remarks', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromStatus()
    {
        return $this->belongsTo(ApplicationStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(ApplicationStatus::class, 'to_status_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }
}
