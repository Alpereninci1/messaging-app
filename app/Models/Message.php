<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'to', 'content', 'status', 'external_message_id', 'response_code', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
