<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPostComment extends Model
{
    use HasFactory;

    protected $table = 'master_post_comments';

    protected $fillable = [
        'comment',
        'status',
        'replied_comment',
        'parent_id',
        'user_id',
        'post_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo(MasterPost::class, 'post_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(MasterPostComment::class, 'parent_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(MasterPostComment::class, 'parent_id', 'id')->where('status', 'active');
    }
}
