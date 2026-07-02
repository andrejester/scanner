<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterFaq extends Model
{
    use HasFactory;

    protected $table = 'master_faqs';

    protected $fillable = [
        'question',
        'answer',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'string',
        'order' => 'integer',
    ];
}
