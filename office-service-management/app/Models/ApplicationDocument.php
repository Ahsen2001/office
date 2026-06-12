<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'person_id',
        'document_type_id',
        'uploaded_by',
        'verified_by',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'verification_remarks',
        'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }
}
