<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPortofolioCategory extends Model
{
    use HasFactory;

    protected $table = 'master_portofolio_categories';

    protected $fillable = [
        'title',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function portofolios()
    {
        return $this->hasMany(MasterPortofolio::class, 'category_id', 'id');
    }
}
