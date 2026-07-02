<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterVideo extends Model
{
    use HasFactory;

    protected $table = 'master_videos';

    protected $fillable = [
        'title',
        'slug',
        'id_kategori',
        'tanggal',
        'deskripsi',
        'video',
        'youtube',
        'source_type',
        'status',
        'dibaca',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'string',
        'dibaca' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(MasterVideoCategory::class, 'id_kategori', 'id');
    }
}
