<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSambutanDirektur extends Model
{
    use HasFactory;

    protected $table = 'master_sambutan_direkturs';

    protected $fillable = [
        'nama_direktur',
        'jabatan',
        'sambutan',
        'foto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
