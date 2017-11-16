<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\Admin\Administrator;
use App\Models\AspectRadio;
use App\Models\Channel;
use App\Models\ChannelFragment;
use App\Models\Fragment;
use App\Models\FragmentTemporary;
use App\Models\FragmentType;
use App\Models\FragmentTypeFragment;
use App\Models\KeywordFragment;
use App\Models\Keywords;
use App\Models\Make\MakeChartletFile;
use App\Models\Make\MakeFontFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\Storyboard;
use App\Models\StoryboardTemporary;
use App\Models\SubtitleTemporary;
use App\Models\User\Subtitle;
use CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;



class FodderController extends Controller
{


    private $protocol = 'http://';

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
     * 发布片段——基本信息
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


//            dd($olddata);
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
                        $data['keyword']['keyword'.$k]=$keyWord->keyword;
                    }
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
                        $data['channel']['type'.$k] = $channel->name;
                    }
//                    dd($olddata->channel);
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
                    'keyword' => '',
                    'channel' => '',

                ];
                $aspectradio = AspectRadio::get();
                foreach ($aspectradio as $k => $item)
                {
                    array_push($data['aspect_radio'],$item->aspect_radio);
                }
            }
            $arr = [];
            array_push($arr,$data);
            return response() -> json(['data'=>$arr], 200);
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
                $type['type'.$k] = $v->name;
            }
            $arr=[];
            array_push($arr,$type);
            return response() -> json(['data'=>$arr], 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }


    /**
     * 发布片段——分镜等资源    数据格式要改变
     */


    public function isserFragmentResource(Request $request)
    {
        try {

            //  管理员信息
            $admin = Auth::guard('api')->user();


            // 取出user_id;
            $admin_info = Administrator::with('hasOneUser')->where('id', $admin->id)->firstOrFail(['user_id']);
            if(empty($request->get('name')) || empty($request->get('aspect_radio')) || empty($request->get('duration')) || empty($request->get('integral')) || empty($request->get('vipfree')) || empty($request->get('channel')))
            {
                //判断在片段暂存表中有没有信息

                $olddata = FragmentTemporary::select(['cover', 'bgm','volume'])->where('user_id', '=', $admin_info->user_id)->first();

                if (!empty($olddata->cover)) {

                    //  如果已有数据，则返回该数据

                    //  取出分镜与相对应的特效

                    //  判断每条分镜是否有特效   如果有特效，则取出，没有返回空值

                    $storyboards = FragmentTemporary::where('user_id', '=', $admin_info->user_id)->first()->hasManyStoryboardTemporary()->get();


                    $resourceData = [
                        'cover' => $olddata->cover,
                        'bgm' => $olddata->bgm,
                        'volume' => $olddata->volume,
                    ];

                    foreach ($storyboards as $k => $item) {
                        $resourceData['storybords']['storyborad'.$k]['name'] = $item->name;
                        $resourceData['storybords']['storyborad'.$k]['address'] = $item->address;
                        $resourceData['storybords']['storyborad'.$k]['address2'] = $item->address2;
                        $resourceData['storybords']['storyborad'.$k]['speed'] = $item->speed;
                        $resourceData['storybords']['storyborad'.$k]['isliveshot'] = $item->isliveshot;

                    }


                } else {
                    return response()->json(['message'=>'数据不合法'],200);
                }
            }else{


                //  判断片段暂存表中是否有同名片段
                $re = FragmentTemporary::where('name', $request->name)->first();
                if ($re) {
                    //  如果有返回已有改名字的片段
                    return ['error' => '已经存在该描述'];
                } else {
                    $data['name'] = $request->get('name');
                    $data['aspect_radio'] = $request->get('aspect_radio');
                    $data['duration'] = $request->get('duration');
                    $data['integral'] = $request->get('integral');
                    $data['address_country'] = $request->get('address_country');
                    $data['address_province'] = $request->get('address_province');
                    $data['address_city'] = $request->get('address_city');
                    $data['address_county'] = $request->get('address_county');
                    $data['address_street'] = $request->get('address_street');
                    $data['vipfree'] = $request->get('vipfree');

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
//                    print_r($fragment_temporary_id1);
                    //  存入片段表与关键词表的中间表中
//                    $keywords = explode('|', $request->get('keyword'));
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


                        $keywordFragment = new KeywordFragment;
                        $keywordFragment->keyword_id = $keyword_id;
                        $keywordFragment->fragment_temporary_id = $fragment_temporary_id;
                        $keywordFragment->time_add = time();
                        $keywordFragment->time_update = time();
                        $keywordFragment->save();


                    }

                    //  将频道信息存入频道片段中间表中
                    $channels = explode('|', $request->get('channel'));
//                    $channels = $request->get('channel');
                    foreach ($channels as $k => $item) {
                        $fragment_temporary_id = $fragment_temporary_id1;
                        $channel = FragmentType::where('name', '=', $item)->get();
                        foreach($channel as $k => $v)
                        {
                            $channel_id = $v->id;
                        }
                        $channelFragment = new FragmentTypeFragment;
                        $channelFragment->fragmentType_id = $channel_id;
                        $channelFragment->fragment_temporary_id = $fragment_temporary_id;
                        $channelFragment->time_add = time();
                        $channelFragment->time_update = time();
                        $channelFragment->save();
                    }


                    //  如果没有数据则返回空数组
                    $resourceData = [
                        'cover' => '',
                        'bgm' => '',
                        'volume' => '',
                        'storyboards' => '',

                    ];
//                        dd($resourceData);


                    //            dd(111);


                    DB::commit();

                    //            dd($request->all());
                }
            }



            $arr = [];
            array_push($arr,$resourceData);
            return response() -> json(['data'=>$arr], 200);
        }catch (ModelNotFoundException $e) {
            DB::rollBack();
        return response()->json(['error' => 'not_found'], 404);
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加字幕页面-存入分镜暂存表分镜
     */
    public function issueFragmentAddSubtitle(Request $request)
    {
        try{

            $cover = $request->get('cover');
            $net_address = $request->get('net_address');
            $bgm = $request->get('bgm');
            $volume = $request->get('volume');
            $storyboard = $request->get('storyboard');

            if(empty($cover) || empty($bgm) || empty($volume) || empty($storyboard))
            {
                return response()->json(['error'=>'数据不合法'],200);
            }
            $admin = Auth::guard('api')->user();
            $id = $admin->id;
            $user_id = Administrator::find($id)->hasOneUser->id;
            DB::beginTransaction();
            $fragmentTempoary = FragmentTemporary::where('user_id','=',$user_id)->first();
            $fragmentTempoary -> cover = $cover;
            $fragmentTempoary -> bgm = $bgm;
            $fragmentTempoary -> net_address = $net_address;
            $fragmentTempoary -> volume = $volume;
            $fragmentTempoary -> time_update = time();
            $fragmentTempoary -> save();
            foreach($storyboard as $k => $v)
            {
                $storyboardTempoary = new StoryboardTemporary;
                $storyboardTempoary -> name = $v['name'];
                $storyboardTempoary -> speed = $v['speed'];
                $storyboardTempoary -> address = $v['address'];
                $storyboardTempoary -> address2 = $v['address2'];
                $storyboardTempoary -> isliveshot = $v['isliveshot'];
                $storyboardTempoary -> time_add = time();
                $storyboardTempoary -> time_update = time();
                $storyboardTempoary -> fragment_id = $fragmentTempoary->id;
                $storyboardTempoary -> sort = 1+$k;
                $storyboardTempoary -> save();
            }
            $font = MakeFontFile::where('active','=','1')->get();
            $font1 = [];
            foreach($font as $k => $v)
            {
                $font2['name'] = $v->name;
                array_push($font1,$font2);
            }
            $fragment_id = $fragmentTempoary->id;
            DB::commit();
            return response()->json(['message'=>'成功','font'=>$font,'id'=>$fragment_id],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除分镜
     */
    public function issueFragmentDeleteStoryboard(Request $request)
    {
        try{
            $key = $request->get('key');
            CloudStorage::webDeleteVideo($key);
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 显示发布的页面
     */
    public function issue(Request $request)
    {
       try{

           $fragment_id = $request->get('id');
           $subtitle = $request->get('subtitle');

           DB::beginTransaction();
           foreach($subtitle as $k => $v)
           {
               $name = $v['name'];
               $content = $v['content'];
               $englishContent = $v['englishContent'];
               $start_time = $v['start_time'];
               $stop_time = $v['stop_time'];
               $font_id = $v['font_id'];
               $slowInAndOut = $v['slowIntAndOut'];
               $font_size = $v['font_size'];
               if(empty($name) || empty($content) || empty($start_time) || empty($stop_time) || empty($font_id) || empty($slowInAndOut) || empty($font_size)){
                   return response()->json(['message'=>'数据不合法'],200);
               }
               $subtitleTemporary = new SubtitleTemporary;
               $subtitleTemporary -> name = $name;
               $subtitleTemporary -> content = $content;
               $subtitleTemporary -> englishcontent = $englishContent;
               $subtitleTemporary -> start_time = $start_time;
               $subtitleTemporary -> end_time = $stop_time;
               $subtitleTemporary -> font_id = $font_id;
               $subtitleTemporary -> fragment_id = $fragment_id;
               $subtitleTemporary -> time_add = time();
               $subtitleTemporary -> time_update = time();
               $subtitleTemporary -> slowInAndOut = $slowInAndOut;
               $subtitleTemporary -> font_size = $font_size;
               $subtitleTemporary -> save();
           }

           $data1 = FragmentTemporary::find($fragment_id);

           $fragment = [
               'cover' => $data1->cover,
               'description' => $data1->name,
               'aspect_radio' => $data1->aspect_radio,
               'duration' => $data1->duration,
               'intergral' => $data1->intergral,
               'address' => $data1->address_country.$data1->address_province.$data1->address_city.$data1->address_county.$data1->address_street,
               'volume' => $data1->volume,
               'id' => $data1->id

           ];

           foreach($data1->keyWord as $k => $v)
           {

                $fragment['keyword']['keyword'.$k]=$v->keyword;
           }

//           $fragment['keyword'] = implode(',',$fragment['keyword']);
           if(empty($data1->bgm)){
               $fragment['bgm'] = '有';
           }else{
               $fragment['bgm'] = '空';
           }
           $fragment['storyboard'] = FragmentTemporary::find($fragment_id)->hasManyStoryboardTemporary()->get()->count();
           $data2 = FragmentTemporary::find($fragment_id)->hasManySubtitleTemporary()->get();
           foreach ($data2 as $k => $v)
           {
               $fragment['subtitle']['subtitle'.$k]=$v->start_time.'-'.$v->end_time.' '.$v->content.$v->englishcontent;
           }
           DB::commit();
           $arr = [];
           array_push($arr,$fragment);
           return response()->json(['fragment'=>$arr],200);

       }catch (ModelNotFoundException $e){
           DB::rollBack();
           return response()->json(['error'=>'not_found'],404);
       }
    }

    public function doissue(Request $request)
    {
        try{
            $keys1 = [];
            $keys2 = [];
            $keys3 = [];
            DB::beginTransaction();
            $id = $request->get('id');
            $data1 = FragmentTemporary::find($id);
            $fragment = new Fragment;
            $fragment -> name = $data1->name;
            $fragment -> user_id = $data1->user_id;
            $fragment -> aspect_radio = $data1->aspect_radio;
            $fragment -> duration = $data1->duration;
            $fragment -> net_address = $this->protocol.'v.cdn.hivideo.com/'.$data1->net_address;
            $fragment -> cover = $this->protocol.'img.cdn.hivideo.com/'.$data1->cover;
            $fragment -> bgm = $this->protocol.'s.cdn.hivideo.com/'.$data1->bgm;
            $fragment -> volume = $data1->volume;
            $fragment -> address_province = $data1->address_province;
            $fragment -> address_city = $data1->address_city;
            $fragment -> address_county = $data1->address_county;
            $fragment -> address_street = $data1->address_street;
            $fragment -> intergral = $data1->intergral;
            $fragment -> address_country = $data1->address_country;
            $fragment -> vipfree = $data1->vipfree;
            $fragment -> time_add = time();
            $fragment -> time_update = time();
            $fragment -> size = $data1->size;
            $fragment -> save();
            array_push($keys2,$data1->net_address);
            array_push($keys1,$data1->cover);
            array_push($keys3,$data1->bgm);
            $fragment_id = $fragment->id;
            FragmentType::where('fragment_temporary_id','=',$id)->update(['fragment_id'=>$fragment_id]);
            KeywordFragment::where('fragment_temporary_id','=',$id)->update(['fragment_id'=>$fragment_id]);
            $data2 = StoryboardTemporary::where('fragment_id','=',$id)->get();
            foreach ($data2 as $k=>$v)
            {
                $storyboard = new Storyboard;
                $storyboard -> name = $v->name;
                $storyboard -> speed = $v->speed;
                $storyboard -> address = $this->protocol.'v.cdn.hivideo.com/'.$v->address;
                $storyboard -> fragment_id = $fragment_id;
                $storyboard -> time_add = time();
                $storyboard -> time_update = time();
                $storyboard -> isliveshot = $v->isliveshot;
                $storyboard -> address2 = $this->protocol.'v.cdn.hivideo.com/'.$v->address2;
                $storyboard -> size = $v->size;
                $storyboard -> sort = $v->sort;
                $storyboard -> save();
                array_push($keys2,$v->address,$v->address2);
            }
            $data3 = SubtitleTemporary::where('fragment_id','=',$id)->get();
            foreach ($data3 as $k => $v)
            {
                $subtitle = new Subtitle;
                $subtitle -> name = $data3->name;
                $subtitle -> content = $data3->content;
                $subtitle -> start_time = $data3->start_time;
                $subtitle -> end_time = $data3->end_time;
                $subtitle -> time_add = time();
                $subtitle -> time_update = time();
                $subtitle -> font_id = $data3->font_id;
                $subtitle -> fragment_id = $fragment_id;
                $subtitle -> englishcontent = $data3->englishcontent;
                $subtitle -> slowInAndOut = $data3->slowInAndOut;
                $subtitle -> font_size = $data3->font_size;
                $subtitle -> save();
            }
            $keyPairs1 = array();
            $keyPairs2 = array();
            $keyPairs3 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key."_copy";
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key."_copy";
            }
            foreach ($keys3 as $key)
            {
                $keyPairs3[$key] = $key."_copy";
            }
            $data1->delete();
            $data2->delete();
            $data3->delete();
            $srcbucket = 'hivideo-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-video';
            $destbucket3 = 'hivideo-audio';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket,$destbucket2);
            $message3 = CloudStorage::copyfile($keyPairs3,$srcbucket,$destbucket3);
            DB::commit();
            return response()->json(['message'=>'成功',$message1,$message2,$message3],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
