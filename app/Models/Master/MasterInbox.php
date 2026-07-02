<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterInbox extends Model
{
    use HasFactory;

    protected $table = 'master_inboxes';

    protected $fillable = [
        'name',
        'subject',
        'email',
        'phone',
        'message',
        'ip_address',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }
}
