<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPostCategory extends Model
{
    use HasFactory;

    protected $table = 'master_post_categories';

    protected $fillable = [
        'title',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function posts()
    {
        return $this->hasMany(MasterPost::class, 'post_cat_id', 'id');
    }
}
