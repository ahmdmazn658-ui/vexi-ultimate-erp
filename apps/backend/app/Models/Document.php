<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'file_path', 'original_name', 'mime_type', 'file_size', 'category',
        'documentable_id', 'documentable_type', 'uploaded_by',
    ];

    /**
     * الكيان اللي المستند مرتبط بيه (Project, Contract, Employee, Customer...)
     * أي موديل عايز يقبل مستندات، يضيف بس:
     *   public function documents(): MorphMany { return $this->morphMany(Document::class, 'documentable'); }
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
