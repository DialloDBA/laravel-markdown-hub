<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadmeFile extends Model
{
    protected $fillable = ['user_id', 'folder_id', 'name', 'content', 'original_path'];

    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
    
    public function getRenderedContentAttribute(): string
    {
        $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter();
        return (string) $converter->convert($this->content);
    }
}
