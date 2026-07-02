<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterFileScannerLog extends Model
{
    use HasFactory;

    protected $table = 'master_file_scanner_logs';

    protected $fillable = [
        'file_path',
        'file_name',
        'file_size',
        'file_hash',
        'threat_level',
        'threat_type',
        'suspicious_patterns',
        'scan_result',
        'is_quarantined',
        'scanned_by',
        'scanned_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'suspicious_patterns' => 'json',
        'is_quarantined' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function scannedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'scanned_by', 'id');
    }
}
