<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\HotSearch;
use App\Models\Keywords;
use App\Models\Word_filter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    //
    public function index()
    {
        try{
            /**
             * 热门搜索
             */
            $hotsearch = HotSearch::select(['hot_word'])->where('active',1)->orderBy('sort','desc')->get();
            $hotsearch2 = [];
//            dd($hotsearch->sort);
            foreach ($hotsearch as $key => $item)
            {
                $hotsearch2[] = $item->hot_word;
            }
//                    dd($hotsearch2);

            /**
             * 今日热点
             */

            //  关键词库的热词今日热词排行
            $dayhot1 = Keywords::select(['keyword','count_day'])->orderBy('count_day','desc')->paginate(10);
            //  过滤词库的今日热词排行
            $dayhot2 = Word_filter::select(['keyword','count_day'])->orderBy('count_day','desc')->paginate(10);
            //  用来存放处理后的关键词库今日热词
            $dayhot3 = [];
            //  用来存放处理后的过滤词库的今日热词
            $dayhot4 = [];
            //  处理关键词库的今日热词
            foreach ($dayhot1 as $key => $item)
            {
                $dayhot3[$item->keyword] = $item->count_day;
            }
            //  处理过滤词库的今日热词
            foreach($dayhot2 as $key => $item)
            {
                $dayhot4[$item->keyword] = $item->count_day;
            }
            //  合成今日热词
            $dayhot5 = array_merge($dayhot3,$dayhot4);
            //  排序
            arsort($dayhot5);
            //  最终今日热词
            $dayhot5 = array_slice($dayhot5,0,10,true);



            /**
             * 一周热点
             */
            //  关键词库的热词一周热词排行
            $weekhot1 = Keywords::select(['keyword','count_week'])->orderBy('count_week','desc')->paginate(10);
            //  过滤词库的一周热词排行
            $weekhot2 = Word_filter::select(['keyword','count_week'])->orderBy('count_week','desc')->paginate(10);
            //  用来存放处理后的关键词库一周热词
            $weekhot3 = [];
            //  用来存放处理后的过滤词库的一周热词
            $weekhot4 = [];
            //  处理关键词库的一周热词
            foreach ($weekhot1 as $key => $item)
            {
                $weekhot3[$item->keyword] = $item->count_week;
            }
            //  处理过滤词库的一周热词
            foreach($weekhot2 as $key => $item)
            {
                $weekhot4[$item->keyword] = $item->count_week;
            }
            //  合成一周热词
            $weekhot5 = array_merge($weekhot3,$weekhot4);
            //  排序
            arsort($weekhot5);
            //  最终一周热词
            $weekhot5 = array_slice($weekhot5,0,10,true);
//        dd($weekhot5);
            /**
             * 处理每日热搜，与一周热搜对比，找出新元素
             */

            $array = array_diff($dayhot5,$weekhot5);
            $dayhot6 = [];
            foreach($dayhot5 as $key => $item)
            {
                $dayhot6[$key]['num'] = $item;
                if(array_key_exists($key,$array))
                {
                    $dayhot6[$key]['new'] = 1;
                }else{
                    $dayhot6[$key]['new'] = 0;
                }

            }
//            dd($dayhot6);

            /**
             * 搜索热点 最后要加上有效搜索，然后按有效搜索排序  未完成，项目最后完成
             */
            //  关键词库的热词排行
            $sumhot1 = Keywords::select(['keyword','count_sum'])->orderBy('count_sum','desc')->paginate(10);
            //  过滤词库的排行
            $sumhot2 = Word_filter::select(['keyword','count_sum'])->orderBy('count_sum','desc')->paginate(10);
            //  用来存放处理后的关键词库
            $sumhot3 = [];
            //  用来存放处理后的过滤词库的
            $sumhot4 = [];
            //  处理关键词库的
            foreach ($sumhot1 as $key => $item)
            {
                $sumhot3[$item->keyword] = $item->count_sum;
            }
            //  处理过滤词库的
            foreach($sumhot2 as $key => $item)
            {
                $sumhot4[$item->keyword] = $item->count_sum;
            }
            //  合成
            $sumhot5 = array_merge($sumhot3,$sumhot4);
            //  排序
            arsort($sumhot5);
            //  最终
            $sumhot5 = array_slice($sumhot5,0,10,true);
//            dd($hotsearch2);
//            dd($dayhot6);
//            dd($weekhot5);
            dd($sumhot5);

            /**
             * 返回数据
             */
            return response()->json([
                //  热门搜索
                'hotsearch' => count($hotsearch2) ? $hotsearch2 : null,
                //  今日搜索
                'dayhot' => count($dayhot6) ? $dayhot6 : null,
                //  一周搜索
                'weekhot' => count($weekhot5) ? $weekhot5 : null,
                //  搜索热点
                'sumhot' => count($sumhot5) ? $sumhot5 : null,
            ]);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }
}
