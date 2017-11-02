<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\Admin\Administrator;
use App\Models\AspectRadio;
use App\Models\Channel;
use App\Models\ChannelFragment;
use App\Models\FragmentTemporary;
use App\Models\FragmentType;
use App\Models\FragmentTypeFragment;
use App\Models\KeywordFragment;
use App\Models\Keywords;
use App\Models\Make\MakeChartletFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\StoryboardTemporary;
use App\Models\SubtitleTemporary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;

class FodderController extends Controller
{
    //
    /**
     * 平台看板   还差包装接口
     */

    public function index()
    {
        //  模板文件的总大小
        $templateSumSize = MakeTemplateFile::sum('size');
        //  贴图文件的总大小
        $chartletSumSize = (MakeChartletFile::sum('size'))/(1024*1024*1024);
        //  特效文件总大小
        $effectSumSize = (MakeChartletFile::sum('size'))/(1024*1024*1024);
        //  片段文件总大小  暂无先设置为0
        $fragmentSumSize = 0;
        //  总大小
        $sum = $templateSumSize+$chartletSumSize+$effectSumSize+$fragmentSumSize;
//        dd($sum);
        /**
         * 总览
         */
        $masterData = [
            'template'=>floor(($templateSumSize/$sum)*100).'%',
            'fragment'=>floor(($fragmentSumSize/$sum)*100).'%',
            'effect'=>floor(($effectSumSize/$sum)*100).'%',
            'chartlet'=>floor(($chartletSumSize/$sum)*100).'%',
            ];
//        dd($masterData);

        /**
         * 模板
         */

        /**
         * 日发布量对比
         */
        // 一天的时间戳
        $oneday = 60*60*24;
        //  今天0点的时间戳
        $todayDate = strtotime('today');
        //  今天此时刻的时间戳
        $todayNowDate = time();
        //  昨天零点的时间戳
        $yesterdayDate = $todayDate-$oneday;
        //  昨天此时刻的时间戳
        $yesterdayNowDate = $todayNowDate-$oneday;
        /**
         * 日官方发布对比量
         */
        //  今日官方发布量
        $templateDayOfficial1 = MakeTemplateFile::where('official','=','0')->where('time_add','>',"$todayDate")->where('time_add','<',"$todayNowDate")->count('name');
        //  昨日官方发布量
        $templateDayOfficial2 = MakeTemplateFile::where('official','=','0')->where('time_add','>',"$yesterdayDate")->where('time_add','<',"$yesterdayNowDate")->count('name');
        //  日官方发布对比量
        $templateDayOfficial = $templateDayOfficial1-$templateDayOfficial2;
        /**
         * 日用户发布对比量
         */
        //  今日用户发布量
        $templateDayUser1 = MakeTemplateFile::where('official','=','1')->where('time_add','>',"$todayDate")->where('time_add','<',"$todayNowDate")->count('name');
        //  昨日用户发布量
        $templateDayUser2 = MakeTemplateFile::where('official','=','1')->where('time_add','>',"$yesterdayDate")->where('time_add','<',"$yesterdayNowDate")->count('name');
        //  日用户发布对比量
        $templateDayUser = $templateDayUser1-$templateDayUser2;

        /**
         * 日发布大小对比
         */
        //  今日发布量
        $templateThisDaySize = MakeTemplateFile::where('')->where('time_add','>',"$todayDate")->where('time_add','<',"$todayNowDate")->sum('size');
        $templateThisDaySize = $templateThisDaySize/(1024*1024*1024);

        //  昨日发布量
        $templateYesterdaySize = MakeTemplateFile::where('time_add','>',"$yesterdayDate")->where('time_add','<',"$yesterdayNowDate")->count('name');
        $templateYesterdaySize = $templateYesterdaySize/(1024*1024*1024);

        //  日发布量对比
        $templateDaySize = $templateThisDaySize - $templateYesterdaySize;

        /**
         *  周发布量对比
         */
        //  一周的时间戳
        $oneWeek = 60*60*24*7;
        //  本周一的时间戳
        $thisMonday = strtotime( "previous monday" );
        //  现在的时间戳
        $time = time();
        //  上周一的时间戳
        $lastMonday = $thisMonday-$oneWeek;
        //  上周此时的时间戳
        $lastWeekTime = $time-$oneWeek;

        /**
         *  周官方发布对比量
         */
        //  本周官方发布量
        $templateWeekOfficial1 = MakeTemplateFile::where('official','=','0')->where('time_add','>',"$thisMonday")->where('time_add','<',"$time")->count('id');
        //  上周官方发布量
        $templateWeekOfficial2 = MakeTemplateFile::where('official','=','0')->where('time_add','>',"$lastMonday")->where('time_add','<',"$lastWeekTime")->count('id');
        //  官方发布对比量
        $templateWeekOfficial = $templateWeekOfficial1-$templateWeekOfficial2;

        /**
         * 周用户发布对比量
         */

        //  本周用户发布量
        $templateWeekUser1 = MakeTemplateFile::where('official','=','1')->where('time_add','>',"$thisMonday")->where('time_add','<',"$time")->count('id');
        //  上周用户发布量
        $templateWeekUser2 = MakeTemplateFile::where('official','=','1')->where('time_add','>',"$lastMonday")->where('time_add','<',"$lastWeekTime")->count('id');
        //  用户发布对比量
        $templateWeekUser = $templateWeekUser1 - $templateWeekUser2;

        /**
         * 周发布量对比
         */

        //  本周发布大小
        $templateThisWeekSize = MakeTemplateFile::where('time_add','>',"$thisMonday")->where('time_add','<',"$time")->sum('size');
        //  上周发布大小
        $templateLastWeekSize = MakeTemplateFile::where('time_add','>',"$lastMonday")->where('time_add','<',"$lastWeekTime")->sum('size');
        //  周发布比较量
        $templateWeekSize = $templateThisWeekSize - $templateLastWeekSize;



        /**
         * 月发布量对比
         */
        //  本月1号的时间戳
        $a =  date('Y-m',time());
        $thisMonthFirstDay = strtotime($a.'-1 0:0:0');
        //  上月最后一天的时间戳
        $lastMonthFinalyDay = $thisMonthFirstDay-1;
        //  上月1号的时间戳
        $b = date('Y-m',$lastMonthFinalyDay);
        $lastMonthFirstDay = strtotime($b.'-1 0:0:0');

//        dd(date('Y-m-d H:i:s',$lastMonthFirstDay));
        //  上月今天的时间戳
        $c = date('d H:i:s',time());

        $lastMonthToday = strtotime($b.'-'.$c);
//        dd(date('Y-m-d H:i:s',$lastMonthToday));
        /**
         * 月官方发布比较量
         */
        //  上月官方发布量
        $templateMonthOfficial2 = MakeTemplateFile::where('official','=','0')->where('time_add','>',"$lastMonthFirstDay")->where('time_add','<',"lastMonthToday")->count('id');
        //  本月官方发布量
        $templateMonthOfficial1 = MakeTemplateFile::Where('official','=','0')->where('time_add','>',"$thisMonthFirstDay")->where('time_add','<',"$time")->count('id');
        //  本月官方发布比较量
        $templateMonthOfficial = $templateMonthOfficial1 - $templateMonthOfficial2;
//        dd($templateMonthOfficial);

        /**
         * 月用户发布比较量
         */

        //  上月用户发布量
        $templateMonthUser2 = MakeTemplateFile::where('official','=','1')->where('time_add','>',"$lastMonthFirstDay")->where('time_add','<',"lastMonthToday")->count('id');

        //  本月用户发布量
        $templateMonthUser1 = MakeTemplateFile::Where('official','=','1')->where('time_add','>',"$thisMonthFirstDay")->where('time_add','<',"$time")->count('id');

        //  月用户发布比较量
        $templateMonthUser = $templateMonthUser1 - $templateMonthUser2;

        //  本月发布大小
        $templateThisMonthSize = MakeTemplateFile::where('time_add','>',"$thisMonthFirstDay")->where('time_add','<',"$time")->sum('size');

        //  上月发布大小
        $temlateLastMonthSize = MakeTemplateFile::where('time_add','>',"$lastMonthFirstDay")->where('time_add','<',"lastMonthToday")->sum('size');

        //  月发布大小比较量
        $templateMontSize = $templateThisMonthSize - $templateLastWeekSize;

        /**
         * 官方发布总数量
         */

        $templateOfficialSum = MakeTemplateFile::where('official','=','0')->count('id');

        /**
         * 用户发布总数量
         */
        $templateUserSum = MakeTemplateFile::where('official','=','1')->count('id');

//        dd($templateUserSum);



















    }

    /**
     * 发布片段——基本信息  未写接口文档
     */
    public function isserFragmentBase()
    {
        try{
            //  管理员信息
            $admin = Auth::guard('api')->user();
//        dd($admin);
            // 取出user_id;
            $admin_info = Administrator::with('hasOneUser')->where('id',$admin->id)->firstOrFail(['user_id']);

            //判断在片段暂存表中有没有信息

            $olddata = FragmentTemporary::where('user_id','=',$admin_info->user_id)->first();

//        $aa = FragmentTemporary::with('keyWord')->where('id',$admin->id)->get();
//            $aa = FragmentTemporary::where('user_id','=',$admin_info->user_id)->first();

//        dd($aa);
//            dd(empty($olddata));
            if(!empty($olddata))
            {
                if($olddata->count())
                {

                    //  如果 有数据则返回下面的数据

                    $data = [

                        'name' => $olddata->name,
                        'aspect_radio' => $olddata->aspect_radio,
                        'duration' => $olddata->duration,
                        'intergral' => $olddata->intergral,

                            'address_country' => $olddata->address_country,
                            'address_province' => $olddata->address_province,
                            'address_city' => $olddata->address_city,
                            'address_county' => $olddata->address_county,
                            'address_street' => $olddata->address_street,
                        'vipfree' => $olddata->vipfree,



                    ];
                    foreach( $olddata->keyWord as $k => $keyWord)
                    {
                        $data['keyword'][$k] = $keyWord->keyword;
//                        print($keyWord);
                    }
//                    dd($olddata->keyWord());
                    if(!empty($olddata->bgm))
                    {
                        $oldSubtitle = FragmentTemporary::with('hasManySubtitleTemporary')->where('user_id',$admin_info->user_id)->first();
                        if(!empty($oldSubtitle))
                        {
                            $data['page'] = 2;
                        }else{
                            $data['page'] = 2;
                        }
                    }else{

                        $data['page'] = 1;
                    }
                    foreach( $olddata->Channel as $k => $channel)
                    {
                        $data['channel'][$k] = $channel->name;
                    }
//            dd($data);
                }
            }else{

                //  如果没有数据则返回下面的数据
                $data = [
                    'page' => 1,
                    'name' => '',
                    'aspect_radio' => [
                        'default' => '16:9',
                    ],
                    'duration' => '00.00.00',
                    'integral' => '0',
                        'address_country' => '',
                        'address_province' => '',
                        'address_city' => '',
                        'address_county' => '',
                        'address_street' => '',
                    'vipfree' => '1',
                    'keyword' => [],
                    'channel' => [],

                ];
                $aspectradio = AspectRadio::get();
                foreach ($aspectradio as $k => $item)
                {
                    $data['aspect_radio'][$k] = $item->aspect_radio;
                }
            }
            return response() -> json($data, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 发布片段-添加分类
     */
    public function isserFragmentAddtype()
    {
        try{
//            //  管理员信息
//            $admin = Auth::guard('api')->user();
//            dd($admin->id);
            $data = FragmentType::where('active','=','1')->get();
            $type = [];
            foreach($data as $k => $v)
            {
                $type[$k] = $v->name;
            }
            return response() -> json($type, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }


    /**
     * 发布片段——分镜等资源  未写接口文档
     */


    public function isserFragmentResource(Request $request)
    {
        try {
            //  管理员信息
            $admin = Auth::guard('api')->user();
            //        dd($admin);
            // 取出user_id;
            $admin_info = Administrator::with('hasOneUser')->where('id', $admin->id)->firstOrFail(['user_id']);
            $data = $request->all();
            //  判断片段暂存表中是否有同名片段
            $re = FragmentTemporary::where('name', $request->name)->first();
            if ($re) {
                //  如果有返回已有改名字的片段
                return ['error' => '已经存在该描述'];
            } else {
                $data['name'] = $request->name;
                $data['aspect_radio'] = $request->aspect_radio;
                $data['duration'] = $request->duration;
                $data['integral'] = $request->integral;
                $data['address_country'] = $request->address_country;
                $data['address_province'] = $request->address_province;
                $data['address_city'] = $request->address_city;
                $data['address_county'] = $request->address_county;
                $data['address_street'] = $request->address_street;
                $data['vipfree'] = $request->vipfree;

                //  将数据存入片段暂存表中
                DB::beginTransaction();
                $newFragment = FragmentTemporary::create([
                    'user_id' => $admin_info->user_id,
                    'name' => $data['name'],
                    'aspect_radio' => $data['aspect_radio'],
                    'duration' => $data['duration'],
                    'address_country' => $data['address_country'],
                    'address_province' => $data['address_province'],
                    'address_city' => $data['address_city'],
                    'address_county' => $data['address_county'],
                    'address_street' => $data['address_street'],
                    'vipfree' => $data['vipfree'],
                    'time_add' => time(),
                    'time_update' => time()

                ]);
                //  取出刚刚插入到片段暂存表中的数据的id
                $fragment_temporary_id1 = $newFragment->id;
                print_r($fragment_temporary_id1);
                //  存入片段表与关键词表的中间表中
                $keywords = explode('|', $request->keyword);
                foreach ($keywords as $k => $item) {
                    $fragment_temporary_id = $fragment_temporary_id1;
                    $keyword = Keywords::where('keyword', $item)->first();
                    if ($keyword) {
                        $keyword_id = $keyword->id;

                    } else {
                        $newKeyword = Keywords::create([
                            'keyword' => $item,
                            'create_at' => time(),
                            'update_at' => time()
                        ]);
                        $keyword_id = $newKeyword->id;

                    }
                    //                $temporaryKey = KeywordFragment::create([
                    //                    'keyword_id' => $keyword_id,
                    //                    'fragment_temporary_id' => $fragment_temporary_id,
                    //                    'time_add' => time(),
                    //                    'time_update' => time()
                    //                ]);

                    $keywordFragment = new KeywordFragment;
                    $keywordFragment->keyword_id = $keyword_id;
                    $keywordFragment->fragment_temporary_id = $fragment_temporary_id;
                    $keywordFragment->time_add = time();
                    $keywordFragment->time_update = time();
                    $keywordFragment->save();


                }

                //  将频道信息存入频道片段中间表中
                $channels = explode('|', $request->channle);
                foreach ($channels as $k => $item) {
                    $fragment_temporary_id = $fragment_temporary_id1;
                    $channel = FragmentType::where('name', '=', $item)->first();
                    $channel_id = $channel->id;
                    $channelFragment = new FragmentTypeFragment;
                    $channelFragment->fragmentType_id = $channel_id;
                    $channelFragment->fragment_temporary_id = $fragment_temporary_id;
                    $channelFragment->time_add = time();
                    $channelFragment->time_update = time();
                    $channelFragment->save();
                }

                //  判断有无之前存入的文件，如果有显示，如果没有则添加新的


                //判断在片段暂存表中有没有信息

                $olddata = FragmentTemporary::select(['cover', 'bgm'])->where('user_id', '=', $admin_info->user_id)->first();
                //            dd($olddata);
                if (!empty($olddata->cover)) {

                    //  如果已有数据，则返回该数据

                    //  取出分镜与相对应的特效

                    //  判断每条分镜是否有特效   如果有特效，则取出，没有返回空值

                    $storyboards = FragmentTemporary::with('hasManyStoryboardTemporary')->where('user_id', '=', $admin_info->user_id)->get();


                    $resourceData = [
                        'cover' => $olddata->cover,
                        'bgm' => $olddata->bgm,
                        'volume' => $olddata->volume,
                    ];

                    foreach ($storyboards as $k => $item) {
                        $resourceData['storyboards'][$k]['name'] = $item->name;
                        $resourceData['storyboards'][$k]['address'] = $item->address;
                        $effect = StoryboardTemporary::where('id', '=', $item->id)->first();
                        //                    dd($effect['name']);
                        $name = $effect['name'];
                        $address = $effect['address'];
                        $resourceData['storyboards'][$k]['effects'] = [
                            'name' => $name,
                            'address' => $address,
                        ];

                    }

                    //                dd($resourceData);
                } else {
                    //  如果没有数据则返回空数组
                    $resourceData = [
                        'cover' => '',
                        'bgm' => '',
                        'volume' => '',
                        'storyboards' => [],

                    ];
                    dd($resourceData);
                }

                //            dd(111);


                DB::commit();

                //            dd($request->all());
            }


            return response() -> json($resourceData, 200);
        }catch (ModelNotFoundException $e) {
            DB::rollBack();
        return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }



}
