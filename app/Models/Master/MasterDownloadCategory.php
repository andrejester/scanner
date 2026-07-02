<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDownloadCategory extends Model
{
    use HasFactory;

    protected $table = 'master_download_categories';

    protected $fillable = [
        'title',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function downloads()
    {
        return $this->hasMany(MasterDownload::class, 'id_kategori', 'id');
    }
}
