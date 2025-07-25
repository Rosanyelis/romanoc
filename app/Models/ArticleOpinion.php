<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleOpinion extends Model
{
    protected $fillable = ['article_id', 'user_id', 'opinion', 'url_file'];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }

    public function law()
    {
        return $this->hasOneThrough(Law::class, Article::class, 'id', 'id', 'article_id', 'law_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
