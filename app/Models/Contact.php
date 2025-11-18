<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Mark the contact as read
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the contact as replied
     */
    public function markAsReplied()
    {
        $this->update([
            'status' => 'replied',
        ]);
    }

    /**
     * Archive the contact
     */
    public function archive()
    {
        $this->update([
            'status' => 'archived',
        ]);
    }
}
