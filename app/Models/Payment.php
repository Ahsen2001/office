<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'receipt_no',
        'application_id',
        'person_id',
        'payment_method_id',
        'received_by',
        'amount',
        'status',
        'transaction_reference',
        'remarks',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }
}
