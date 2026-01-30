<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QontakRoom extends Model
{
    protected $fillable = [
        'room_id',
        'agent_id',
        'is_assigned',
        'last_message_at',
        'assigned_at',
    ];

    protected $casts = [
        'is_assigned' => 'boolean',
        'last_message_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    protected $attributes = [
        'is_assigned' => false,
    ];
    public function scopeUnassigned($query)
    {
        return $query->where('is_assigned', false);
    }

    public function scopeAssigned($query)
    {
        return $query->where('is_assigned', true);
    }

    public function scopeRecentlyCreated($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}