<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_no',
        'application_id',
        'person_id',
        'department_id',
        'officer_id',
        'created_by',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'purpose',
        'remarks',
    ];

    protected function casts(): array
    {
        return ['appointment_date' => 'date'];
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
