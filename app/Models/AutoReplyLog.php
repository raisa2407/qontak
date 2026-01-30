<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReplyLog extends Model
{
    protected $fillable = [
        'room_id',
        'message_id',
        'customer_phone',
        'customer_message',
        'matched_keyword',
        'reply_sent',
        'reply_type',
        'is_successful',
        'error_message',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
    ];
}