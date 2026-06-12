<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'person_id', 'application_id', 'channel', 'title', 'message', 'type', 'is_read', 'data', 'read_at', 'sent_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }
}
