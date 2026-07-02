<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLayananKami extends Model
{
    use HasFactory;

    protected $table = 'master_layanan_kamis';

    protected $fillable = [
        'title',
        'deskripsi',
        'icon',
    ];
}
