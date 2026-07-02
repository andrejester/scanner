<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPost extends Model
{
    use HasFactory;

    protected $table = 'master_posts';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'quote',
        'photo',
        'tags',
        'post_cat_id',
        'post_tag_id',
        'added_by',
        'status',
        'dibaca',
    ];

    protected $casts = [
        'status' => 'string',
        'dibaca' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(MasterPostCategory::class, 'post_cat_id', 'id');
    }

    public function tag()
    {
        return $this->belongsTo(MasterPostTag::class, 'post_tag_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(MasterPostComment::class, 'post_id', 'id')->where('status', 'active');
    }

    public function allComments()
    {
        return $this->hasMany(MasterPostComment::class, 'post_id', 'id');
    }
}
