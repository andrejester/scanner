<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBannerAds extends Model
{
    use HasFactory;

    protected $table = 'master_banner_ads';

    protected $fillable = [
        'title',
        'position',
        'image',
        'link',
        'target',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
