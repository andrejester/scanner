<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
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
    // Jika kamu ingin menggunakan timestamp custom, misalnya jika 'created_at' dan 'updated_at' tidak digunakan
    public $timestamps = true;
}
