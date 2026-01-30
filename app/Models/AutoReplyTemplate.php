<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReplyTemplate extends Model
{
    protected $fillable = [
        'type',
        'keyword',
        'message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAssigned($query)
    {
        return $query->where('type', 'assigned');
    }

    public function scopeUnassigned($query)
    {
        return $query->where('type', 'unassigned');
    }

    public function scopeByKeyword($query, $keyword)
    {
        return $query->where('keyword', $keyword);
    }
}