<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\Admin\Administrator;
use App\Models\AspectRadio;
use App\Models\DownloadCost;
use App\Models\Fragment;
use App\Models\FragmentTemporary;
use App\Models\FragmentType;
use App\Models\FragmentTypeFragment;
use App\Models\KeywordFragment;
use App\Models\Keywords;
use App\Models\Make\MakeChartletFile;
use App\Models\Make\MakeTemplateFile;
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
            $admin_info = Administrator::with('hasOneUser')->where('id',$admin->id)->firstOrFail(['user_id']);

            //判断在片段暂存表中有没有信息

            $olddata = FragmentTemporary::where('user_id','=',$admin_info->user_id)->first();


            if(!empty($olddata))
            {

                    //  如果 有数据则返回下面的数据

                    $data = [
                        'id' => $olddata->id,
                        'user_id' => $olddata->user_id,
                        'name' => $olddata->name,
                        'aspect_radio' => $olddata->aspect_radio,
                        'duration' => floor(($olddata->duration)/60).':'.(($olddata->duration)%60),
                        'intergral' => $olddata->intergral,
                        'address_country' => $olddata->address_country,
                        'address_province' => $olddata->address_province,
                        'address_city' => $olddata->address_city,
                        'address_county' => $olddata->address_county,
                        'address_street' => $olddata->address_street,
                        'vipfree' => $olddata->vipfree,
                        'net_address' => $this->protocol.'viedo.ects.cdn.hivideo.com/'.$olddata->net_address,
                        'zip_address' =>  $this->protocol.'file.ects.cdn.hivideo.com/'.$olddata->zip_address,
                        'cover' => $this->protocol.'img.ects.cdn.hivideo.com/'.$olddata->cover,
                        'size' => $olddata->size,
                        'storyboard_count'=>$olddata->storyboard,



                    ];

                    foreach( $olddata->keyWord as $k => $keyWord)
                    {
                        $data['keyword']['keyword'.$k]=$keyWord->keyword;
                    }

                    foreach( $olddata->Channel as $k => $channel)
                    {
                        $data['channel']['type'.$k] = $channel->name;
                    }
//                    dd($olddata->channel);
//            dd($data);

            }else{

                //  如果没有数据则返回下面的数据
                $newfragment = new FragmentTemporary;
                $newfragment ->user_id = $admin_info->user_id;
                $newfragment -> save();
                $data = [
                    'user_id' => $newfragment->user_id,
                    'id' => $newfragment->id,
                ];


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
                array_push($type,['type'=>$v->name]);
            }

            return response() -> json(['data'=>$type], 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 添加画面比例
     */
    public function issueFragmentAddAspectRadio()
    {
        try{
            $data = AspectRadio::get();
            $aspect_radio = [];
            foreach($data as $k => $v)
            {
                array_push($aspect_radio,['value'=>$v->aspect_radio]);
            }
            return response()->json(['data'=>$aspect_radio],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 添加资费
     */
    public function issueFragmentAddIntergal()
    {
        try{
            $data = DownloadCost::get();
            $intergal = [];
            foreach($data as $k => $v)
            {
                array_push($intergal,['intergal'=>$v->details]);
            }
            return response()->json(['data'=>$intergal],200);
        }catch (ModelNotFoundException $e){
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
            $data['id'] = $request->get('id',null);
            $data['name'] = $request->get('name',null);
            $data['aspect_radio'] = $request->get('aspect_radio',null);
            $data['duration'] = $request->get('duration',null);
            $data['integral'] = $request->get('integral',null);
            $data['vipfree'] = $request->get('vipfree',null);
            $data['channel'] = $request->get('channel',null);
            $data['keyword'] = $request->get('keyword',null);
            $data['address_province'] = $request->get('address_province',null);
            $data['address_country'] = $request->get('address_country',null);
            $data['address_city'] = $request->get('address_city',null);
            $data['address_county'] = $request->get('address_county',null);
            $data['address_street'] = $request->get('address_street',null);
            $data['cover'] = $request->get('cover',null);
            $data['net_address'] = $request->get('net_address',null);
            $data['zip_address'] = $request->get('zip_address',null);
            $data['size'] = $request->get('size',null);
            $data['storyboard_num'] = $request->get('storyboard_num',0);
            if(is_null($data['zip_address']) || is_null($data['net_address']) || is_null($data['name']) || is_null($data['id']) || is_null($data['aspect_radio']) || is_null($data['duration']) || is_null($data['integral']) || is_null($data['vipfree']) || is_null($data['channel']) || is_null($data['cover']) ){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $data['duration'] = explode(':',$data['duration']);
            $data['duration'] = ($data['duration'][0]*60) + $data['duration'][1];
            DB::beginTransaction();
            $fragment = FragmentTemporary::find($data['id']);
            $fragment->name = $data['name'];
            $fragment->aspect_radio = $data['aspect_radio'];
            $fragment->duration = $data['duration'];
            $fragment->intergral = $data['integral'];
            $fragment->vipfree = $data['vipfree'];
            $fragment->cover = $data['cover'];
            $fragment->net_address = $data['net_address'];
            $fragment->zip_address = $data['zip_address'];
            $fragment->address_country = $data['address_country'];
            $fragment->address_province = $data['address_province'];
            $fragment->address_city = $data['address_city'];
            $fragment->address_county = $data['address_county'];
            $fragment->address_street = $data['address_street'];
            $fragment->size = $data['size'];
            $fragment->storyboard_num = $data['storyboard_num'];
            $fragment->time_add = time();
            $fragment->time_update = time();
            $fragment->save();

            $label = '';
            if(!is_null($data['keyword'])){
                $keywords = explode('|',$data['keyword']);
                $keywords = array_unique($keywords);
                KeywordFragment::where('fragment_temporary_id','=',$fragment->id)->delete();
                foreach($keywords as $k => $v)
                {
                    $keyword = Keywords::where('keyword',$v)->first();
                    if($keyword){
                        $keyword_id = $keyword->id;
                    }else{
                        $newkeyword = new Keywords;
                        $newkeyword ->keyword = $v;
                        $newkeyword ->create_at = time();
                        $newkeyword ->update_at = time();
                        $newkeyword ->save();
                        $keyword_id = $newkeyword->id;
                    }
                    $keywordFragment = new KeywordFragment;
                    $keywordFragment -> keyword_id = $keyword_id;
                    $keywordFragment -> fragment_temporary_id = $fragment->id;
                    $keywordFragment -> time_add = time();
                    $keywordFragment -> time_update = time();
                    $keywordFragment ->save();
                    $label .= $v.',';
                }
                $label = rtrim($label,',');
            }
            $channels = explode('|',$data['channel']);
            $channels = array_unique($channels);
            $channels = array_slice($channels,0,2,true);
            FragmentTypeFragment::where('fragment_temporary_id',$fragment->id)->delete();
            foreach($channels as $k=> $v)
            {
                $channel_id = FragmentType::where('name',$v)->first()->id;
                $fragmentType = new FragmentTypeFragment;
                $fragmentType ->fragment_temporary_id = $fragment->id;
                $fragmentType ->fragmentType_id = $channel_id;
                $fragmentType ->time_add = time();
                $fragmentType ->time_update  = time();
                $fragmentType ->save();
            }
            $content = [
                'id' => $fragment->id,
                'user_id' => $fragment->user_id,
                'cover' => $this->protocol.'img.ects.cdn.hivideo.com/'.$fragment->cover,
                'description' => $fragment->name,
                'aspect_radio' => $fragment->aspect_radio,
                'duration' => floor(($fragment->duration)/60).':'.(($fragment->duration)%60),
                'integral' => $fragment->intergral,
                'address' => $fragment->address_country.'·'.$fragment->address_province.'·'.$fragment->address_city.'·'.$fragment->address_county.'·'.$fragment->address_street,
                'label' => $label,
                'play' => $this->protocol.'video.ects.cdn.hivideo.com/'.$fragment->net_address,
                'size' => $fragment->size,

            ];
            DB::commit();
            return response()->json(['data'=>$content],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }





    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 执行发布
     */
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
            $fragment -> net_address = 'v.cdn.hivideo.com/'.$data1->net_address;
            $fragment -> cover = 'img.cdn.hivideo.com/'.$data1->cover;
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
            $fragment -> zip_address = 'zip.cdn.hivideo.com/'.$data1->zip_address;
            $fragment -> save();
            array_push($keys2,$data1->net_address);
            array_push($keys1,$data1->cover);
            array_push($keys3,$data1->zip_address);
            $fragment_id = $fragment->id;
            FragmentTypeFragment::where('fragment_temporary_id','=',$id)->update(['fragment_id'=>$fragment_id,'fragment_temporary_id'=>null]);
            KeywordFragment::where('fragment_temporary_id','=',$id)->update(['fragment_id'=>$fragment_id,'fragment_temporary_id'=>null]);
            $keyPairs1 = array();
            $keyPairs2 = array();
            $keyPairs3 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key;
            }
            foreach ($keys3 as $key)
            {
                $keyPairs3[$key] = $key;
            }

            $srcbucket1 = 'hivideo-video-ects';
            $srcbucket2 = 'hivideo-file-ects';
            $srcbucket3 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-video';
            $destbucket3 = 'hivideo-zip';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket3,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket1,$destbucket2);
            $message3 = CloudStorage::copyfile($keyPairs3,$srcbucket2,$destbucket3);
//            print_r($message1.'|'.$message2.'|'.$message3);
            FragmentTemporary::find($id)->delete();
            DB::commit();

            return response()->json(['message'=>'成功',$message1,$message2,$message3],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消发布
     */
    public function cancel(Request $request)
    {
        try{
            $id = $request->get('id');
            DB::beginTransaction();
            FragmentTemporary::find($id)->delete();
            KeywordFragment::where('fragment_temporary_id',$id)->delete();
            FragmentTypeFragment::where('fragment_temporary_id',$id)->delete();
            DB::commit();
            return response()->json(['message'=>'取消成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }

    }

}
