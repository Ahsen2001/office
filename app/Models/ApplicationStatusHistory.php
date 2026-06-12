<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    protected $fillable = ['application_id', 'from_status_id', 'to_status_id', 'changed_by', 'remarks', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
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
}
