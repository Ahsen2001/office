<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'person_code',
        'qr_code_value',
        'barcode_value',
        'qr_code_path',
        'barcode_path',
        'first_name',
        'last_name',
        'full_name',
        'gender',
        'date_of_birth',
        'national_id',
        'passport_no',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'photo_path',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_number',
        'notes',
        'registered_by',
        'registered_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'registered_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function applications()
    {
        return $this->hasMany(ServiceApplication::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function applicationNotes()
    {
        return $this->hasMany(ApplicationNote::class);
    }
}
