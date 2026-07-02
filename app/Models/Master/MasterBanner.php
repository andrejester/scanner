<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBanner extends Model
{
    use HasFactory;

    protected $table = 'master_banners';

    protected $fillable = [
        'title',
        'slug',
        'photo',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
