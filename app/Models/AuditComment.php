<?php
// app/Models/AuditComment.php - добавить связь с медиа

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditComment extends Model
{
    protected $table = 'audit_comments';

    protected $fillable = [
        'audit_id', 'user_id', 'content', 'mentions', 'attachments'
    ];

    protected $casts = [
        'mentions' => 'array',
        'attachments' => 'array'
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь с медиафайлами комментария
    public function media()
    {
        return $this->belongsToMany(AuditMedia::class, 'comment_media', 'comment_id', 'media_id')
            ->withTimestamps();
    }
}
