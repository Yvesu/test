<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordTweets extends Model
{
    public $table = 'keywords_tweet';

    public $fillable = [
        'tweet_id',
        'keyword_id',
        'create_time',
        'update_time',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
}
