<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPortofolio extends Model
{
    use HasFactory;

    protected $table = 'master_portofolios';

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'photo',
        'description',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(MasterPortofolioCategory::class, 'category_id', 'id');
    }

    public function isActive()
    {
        return $this->aktif === 'Y';
    }
}
