<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationNote extends Model
{
    protected $fillable = ['application_id', 'person_id', 'created_by', 'visibility', 'note'];
}
