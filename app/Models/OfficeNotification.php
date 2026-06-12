<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'person_id', 'application_id', 'channel', 'title', 'message', 'data', 'read_at', 'sent_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
