<?php
// app/Models/AuditMedia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AuditMedia extends Model
{
    protected $table = 'audit_media';

    protected $fillable = [
        'audit_id', 'uploaded_by', 'filename', 'original_name',
        'mime_type', 'disk', 'path', 'size', 'media_type',
        'description', 'metadata', 'is_public', 'sort_order'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'size' => 'integer',
        'sort_order' => 'integer'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeForHumansAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $this->size;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
