<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditFields;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Model
{
    use HasFactory, HasAuditFields;

    protected $table = 'file_uploads';

    protected $fillable = [
        'filename',
        'path',
        'extension',
        'mime_type',
        'size',
        'created_by',
        'updated_by'
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
