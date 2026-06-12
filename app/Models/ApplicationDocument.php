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
        'document_title',
        'uploaded_by',
        'verified_by',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'status',
        'remarks',
        'verification_remarks',
        'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
