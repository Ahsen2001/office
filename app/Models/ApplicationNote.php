<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationNote extends Model
{
    protected $fillable = ['application_id', 'person_id', 'created_by', 'visibility', 'note'];

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
