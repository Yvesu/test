<?php

namespace App\Http\Controllers\NewWeb;

use App\Http\Middleware\FilmfestUserRole;
use App\Models\Filmfest\Application;
use App\Models\Filmfest\TweetProductionApplication;
use App\Models\FilmfestUser\FilmfestUserReviewChildLog;
use App\Models\QiniuTest\QiniuCloudTest;
use App\Models\Tweet;
use App\Models\User;
use App\Facades\CloudStorage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Omnipay\Common\Exception\RuntimeExceptionTest;
use Excel;

class TestController extends Controller
{
    //
//    public function test()
//    {
//        $application = Application::find(2);
//        $pdf  =  new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//        // 设置文档信息
//        $pdf->SetCreator('嗨！视频');
//        $pdf->SetAuthor('嗨！视频');
//        $pdf->SetTitle('嗨！视频');
//        $pdf->SetSubject('嗨！视频');
//        $pdf->SetKeywords('TCPDF, PDF, PHP');
//
//        // 设置页眉和页脚信息
//        $pdf->SetHeaderData('hivideo.png', 30, 'www.HiVideo.com', '嗨！视频', [0, 64, 255], [0, 64, 128]);
//        $pdf->setFooterData([0, 64, 0], [0, 64, 128]);
//
//        // 设置页眉和页脚字体
//        $pdf->setHeaderFont(['stsongstdlight', '', '12']);
//        $pdf->setFooterFont(['helvetica', '', '8']);
//
//        // 设置默认等宽字体
//        $pdf->SetDefaultMonospacedFont('courier');
//
//        // 设置间距
//        $pdf->SetMargins(15, 15, 15);//页面间隔
//        $pdf->SetHeaderMargin(5);//页眉top间隔
//        $pdf->SetFooterMargin(10);//页脚bottom间隔
//
//        // 设置分页
//        $pdf->SetAutoPageBreak(false, 25);
//
//        // 设置自动换页
//        $pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
//
//        // 设置图像比例因子
//        $pdf->setImageScale(1.25);
//
//        // 设置默认字体构造子集模式
//        $pdf->setFontSubsetting(true);
//
//        // 设置字体 stsongstdlight支持中文
////        $pdf->SetFont('helvetica', '', 14);
//        $pdf->SetFont('stsongstdlight', '', 8);
//
//        // 添加一页
//        $pdf->AddPage();
//        $duration = floor(($application->duration)/60).'分'.(($application->duration)%60).'秒';
//        $units = $application->filmType()->get();
//        $unit = '';
//        foreach($units as $k => $v)
//        {
//            $unit .= $v->name.'、';
//        }
//        $unit = rtrim($unit,'、');
//        $is_orther_web = $application->is_orther_web === 1 ? '是':'否';
//        $copyright = $application->copyright === 0 ? '完整版权归作者所有':'网站或其他合作方所有';
//        $is_collective = $application->is_collective === 0 ?'否':'是';
//        $create_collective_name = $application->create_collective_name ? $application->create_collective_name:'无';
//        $create_time = date('Y/m/d',$application->create_start_time).' - '.date('Y/m/d',$application->create_end_time);
//        $contact_ways = $application->contactWay()->get();
//        $contact_way = '';
//        foreach ($contact_ways as $k => $v)
//        {
//            $contact_way1 = str_replace('；','、',$v->contact_way);
//            $contact_way .= $contact_way1.'、';
//        }
//        $contact_way = rtrim($contact_way,'、');
//        $schoolAndMajor = $application->university_name.' '.$application->major;
//        $communication_address = $application->communication_address_country.' '.$application->communication_address_province.' '.$application->communication_address_city.' '.$application->communication_address_county.' '.$application->communication_detail_address;
//        $pdf->Ln(5);//换行符
//        $html = "
//            <h2 align=\"center\">第25届北京大学生电影节报名表</h2>
//            <p>作品编号:{$application->number}</p>
//            <p>作品名称:{$application->name}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$application->english_name}</p>
//            <p>作品长度:{$duration}</p>
//            <p>参与单元:{$unit}</p>
//            <p>是否上传过其他视频网站、或获取过相关拍摄资助:{$is_orther_web}<br>{$copyright}</p>
//            <p>是否集体作品:{$is_collective}</p>
//            <p>集体创作人数:{$application->create_people_num}</p>
//            <p>团队创作名称:{$create_collective_name}</p>
//            <p>制作时间:{$create_time}</p>
//            <p>作品简介:</p>
//            <p>{$application->production_des}</p>
//            <p>{$application->production_english_des}</p>
//            <p>作者姓名:{$application->creater_name}</p>
//            <p>导演姓名:{$application->director_name}</p>
//            <p>编剧姓名:{$application->scriptwriter_name}</p>
//            <p>摄影姓名:{$application->photography_name}</p>
//            <p>剪辑姓名:{$application->cutting_name}</p>
//            <p>男主角姓名:{$application->hero_name}</p>
//            <p>女主角姓名:{$application->heroine_name}</p>
//            <p>电话/电子邮件:{$contact_way}</p>
//            <p>所属学校、专业:{$schoolAndMajor}</p>
//            <p>指导老师:{$application->adviser_name}</p>
//            <p>指导老师联系方式:{$application->adviser_phone}</p>
//            <p>通讯地址:{$communication_address}</p>
//            <p>作者简介:{$application->creater_des}</p>
//            <p>其他作者简介及分工介绍:{$application->other_creater_des}</p>
//            <img src=\"http://img.ects.cdn.hivideo.com/0002.png\" align=\"middle\" />
//        ";
//        $pdf->writeHTML($html, true, false, true, false, '');
//        ob_end_clean();
//        //输出PDF
//        $pdf->Output('t.pdf', 'D');//I输出、D下载
//    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 测试生产excel
     */
//    public function test(Request $request)
//    {
//        try{
//            $filmfest_id = 1;
//            $user_id = 1000437;
//            $nickName = User::find($user_id)->nickname;
//            $role = 2;
//            if(is_null($user_id)||is_null($role)){
//                return response()->json(['message'=>'缺少数据'],200);
//            }
//            $data = FilmfestUserReviewChildLog::where('user_id',$user_id)->where('filmfest_id',$filmfest_id)
//                ->orderBy('time_add','desc')->get();
//            $title = [
//                0=>'时间',
//                1=>'行为',
//            ];
//            $export = null;
//            foreach($data as $k => $v)
//            {
//                $time = date('Y年m月d日. H:i',$v->time_add);
//                $content = ($v->doing).'  '.($v->cause);
//                $export[$k][0] = $time;
//                $export[$k][1] = $content;
//            }
//            $data = array_merge($title,$export);
//            $head = $role.$nickName.'操作日志';
//            Excel::create($head,function ($excel) use($data,$head){
//                $excel->sheet($head,function ($sheet) use($data){
//                    $sheet->setWidth(
//                        array(
//                            'A'=>10,
//                            'B'=>30,
//                        )
//                    );
//                    $sheet->rows($data);
//                    ob_end_clean();
//                });
//            })->export('xls');
//
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//
//    }

//    public function test(Request $request)
//    {
//        $tid = 1;
//        $bucket = 'hivideo-video-ects';
//        $key = 'enroll/competition/trailers/1000413/1/20180211142858.mp4';
//        $width = '960';
//        $height = '540';
//        $choice = '';
//        $notice = '';
//        CloudStorage::transcoding_tweet($tid,$bucket,$key,$width,$height,$choice,$notice);
//    }

//    public function test()
//    {
//        $width = 1280;
//        $height = 720;
//        $id = 1854;
//        CloudStorage::joint_tranding_ects($id,1,null,3,$width,$height);
//
//    }

//    public function test()
//    {
//        $a = CloudStorage::listenTranscoding('z0.5a94bc82b9465319005c48ed','hivideo-video-ects');
//        dd($a);
//    }

    public function test()
    {
        $bucket = 'test';
        $id = 1;
        $width = 1280;
        $height = 720;
        $notice = 'http://www.goobird.com/api/test-callback2';
        $key = 'test_title';
        $join_id = 16;
        CloudStorage::change_resolution($join_id,$bucket,$width,$height,$key,$notice);
//        CloudStorage::transcoding_test($id,$bucket,$key,$width,$height,$notice);
//        $a = stripos('adapt/m3u8/gsadfasg','adapt/m3u8');
//        var_dump($a);
    }

    /**
     * 测试七牛回调
     */
    public function testCallback()
    {
        $NotifyData = file_get_contents("php://input");
        $res = json_decode($NotifyData)->code;
        $id = json_decode($NotifyData)->id;
        if ($res === 0 ) {
            $key = json_decode($NotifyData)->items[0]->key;
            $tweet_id = getNeedBetween($key, '&', '&&');
            $new_url = 'test.v.cdn.hivideo.com/' . json_decode($NotifyData)->items[0]->key;
            $new_res = new QiniuCloudTest;
            $new_res -> content = '视频拼接成功，新文件地址为'.$new_url;
            $new_res -> type = '视频拼接';
            $new_res -> persistentId = $id;
            $new_res -> code = json_decode($NotifyData)->items[0]->code;
            $new_res -> time_add = time();
            $new_res -> time_update = time();
            $new_res -> save();
        }elseif($res == 3){
            $key = json_decode($NotifyData)->inputKey;
            $tweet_id = Tweet::where('video','like','%'.$key.'%')->first()->id;
            $new_res = new QiniuCloudTest;
            $new_res -> content = '视频拼接失败，错误原因为：'.json_decode($NotifyData)->items[0]->error;
            $new_res -> type = '视频切片兼转码';
            $new_res -> key = $tweet_id;
            $new_res -> persistentId = $id;
            $new_res -> code = json_decode($NotifyData)->items[0]->code;
            $new_res -> time_add = time();
            $new_res -> time_update = time();
            $new_res -> save();
        }elseif($res == 4){
            $key = json_decode($NotifyData)->items[0]->key;
            $tweet_id = getNeedBetween($key, '&', '&&');
            $new_res = new QiniuCloudTest;
            $new_res -> content = $tweet_id.'视频拼接成功，回调失败错误原因为：'.json_decode($NotifyData)->items[0]->error;
            $new_res -> type = '视频切片兼转码';
            $new_res -> persistentId = $id;
            $new_res -> code = json_decode($NotifyData)->items[0]->code;
            $new_res -> time_add = time();
            $new_res -> time_update = time();
            $new_res -> save();
        }
    }

    public function testCallback2()
    {
        $NotifyData = file_get_contents("php://input");
        $res = json_decode($NotifyData)->code;
        if($res == 0){
            $key = json_decode($NotifyData)->items[0]->key;
            $application_id = getNeedBetween($key, '&', '&&');
            $application = Application::find($application_id);
            $production_id = $application->production_id;
            $production = $application->production()->first();
            $tweet = $production->tweet()->first();
            $tweet_id = $tweet->id;
            $productionApplication = TweetProductionApplication::where('tweet_production_id',$production_id)
                ->where('application_id',$application_id)->first();
            $productionApplication -> status =  1;
            $productionApplication -> time_update = time();
            switch ($key){
                case strstr($key,'title');
                    $productionApplication -> title_temp_name = json_decode($NotifyData)->items[0]->key;
                    break;
                case strstr($key,'tail');
                    $productionApplication -> tail_temp_name = json_decode($NotifyData)->items[0]->key;
                    break;
                default:
                    break;
            }
            $productionApplication -> save();
            $productionApplication_id = $productionApplication->id;
            $notice = 'http://www.goobird.com/api/test-callback';
            CloudStorage::join_ects_test($tweet_id,$productionApplication_id,$notice,$status=3);
        }
    }
}
