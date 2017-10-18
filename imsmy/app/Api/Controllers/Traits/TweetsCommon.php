<?php
namespace App\Api\Controllers\Traits;
use App\Models\TweetActivity;
use Illuminate\Support\Facades\Cache;

trait TweetsCommon
{
    /**
     * 某赛事的动态排名
     *
     * @param $activity_id
     * @return mixed
     */
    public function activityUsersRanking($activity_id)
    {
        $ranking = Cache::remember('activity_ranking_'.$activity_id, 5, function() use($activity_id) {
            return TweetActivity::where('activity_id', $activity_id)
                -> orderBy('like_count', 'DESC')
                -> pluck('tweet_id');
        });

        return $ranking;
    }
}