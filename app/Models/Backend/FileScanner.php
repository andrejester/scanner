<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileScanner extends Model
{
    use HasFactory;

    protected $table = 'file_scanner_logs';

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
        'suspicious_patterns' => 'array',
        'is_quarantined' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'scanned_by');
    }

    public function scopeThreatLevel($query, $level)
    {
        return $query->where('threat_level', $level);
    }

    public function scopeQuarantined($query)
    {
        return $query->where('is_quarantined', true);
    }
}
