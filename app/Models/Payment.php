<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'receipt_no',
        'application_id',
        'person_id',
        'service_id',
        'payment_method_id',
        'received_by',
        'amount',
        'status',
        'payment_date',
        'transaction_reference',
        'remarks',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
