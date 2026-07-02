<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterVideoCategory extends Model
{
    use HasFactory;

    protected $table = 'master_video_categories';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'photo',
        'type',
        'status',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];

    public function videos()
    {
        return $this->hasMany(MasterVideo::class, 'id_kategori', 'id');
    }
}
