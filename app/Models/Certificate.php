<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_certificate';

    protected $fillable = [
        'id_participant',
        'file_url',
        'generated_at',
        'is_sent',
        'sent_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(OrderParticipant::class, 'id_participant');
    }
}
