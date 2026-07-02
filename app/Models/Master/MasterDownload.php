<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDownload extends Model
{
    use HasFactory;

    protected $table = 'master_downloads';

    protected $fillable = [
        'title',
        'title_seo',
        'id_kategori',
        'file',
    ];

    public function category()
    {
        return $this->belongsTo(MasterDownloadCategory::class, 'id_kategori', 'id');
    }
}
