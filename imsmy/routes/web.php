<?php

$api = app('Dingo\Api\Routing\Router');

/**
 * TODO 前期前端用户中心测试，后期再修改，添加用户认证
 */
$api -> version('v1', ['prefix' => 'user','namespace' => 'App\Http\Controllers\Web'], function($api) {

    $api -> post('/info/index','PersonalCenterController@index');
    $api -> post('/tweet/index','PersonalCenterController@tweet');
    $api -> post('/contribution/index','PersonalCenterController@contribution');
    $api -> post('/tweet/hot','PersonalCenterController@hot');
    $api -> post('/tweet/friends','PersonalCenterController@friends');
    $api -> post('/tweet/recommend','PersonalCenterController@recommend');
});

// 支付宝应用网关,同步回调
Route::post('alipay/return','Pay\AlipayController@gateway');

/**
 * 前端页面
 */
//Route::get('/','Web\IndexController@index');

/**
 * 手机网页测试，已停用
 */
Route::get('/tweet/{id}','Web\TweetsController@show')
    ->where('id','[0-9]+');

/**
 * 处理七牛的回调
 */
Route::post('qiniu/screenshot','Admin\Qiniu\CallbackController@screenshot');


/**
 * APP各类协议数据
 */
//Route::group(['prefix' => 'agreement', 'namespace' => 'Web'], function() {
//
//    Route::get('/register','AgreementController@register');
//    Route::get('/report','AgreementController@report');
//    Route::get('/sponsor','AgreementController@sponsor');
//    Route::get('/invest','AgreementController@invest');
//    Route::get('/lease','AgreementController@lease');
//    Route::get('/role','AgreementController@role');
//    Route::get('/rule','AgreementController@rule');
//    Route::get('/index','AgreementController@index');
//});

// 前台路由
Route::group(['namespace' => 'Web'], function() {

    /**
     * 网站首页
     */
    Route::get('/admins','IndexController@index');

    /**
     * APP各类协议数据
     */
    Route::group(['prefix' => 'agreement'], function() {

        Route::get('/register','AgreementController@register');
        Route::get('/report','AgreementController@report');
        Route::get('/sponsor','AgreementController@sponsor');
        Route::get('/invest','AgreementController@invest');
        Route::get('/lease','AgreementController@lease');
        Route::get('/role','AgreementController@role');
        Route::get('/rule','AgreementController@rule');
        Route::get('/index','AgreementController@index');
    });
});

Route::group(['namespace'=>'NewWeb'],function (){

    /**
     * 首页
     */
//    Route::get('/','TestIndexController@index');
    Route::get('/',function (){
        return view('stick');
    });

    Route::get('/user',function (){
        return view('web');
    });
    Route::get('/testuser','Office\WebIndexController@userIndex');

    Route::get('test','TestController@test');

});



Route::get('index1','NewWeb\Test\ProductionController@index1');
$api -> version('v1',function($api) {

//    $api -> group(['namespace' => 'App\Http\Controllers\NewWeb','middleware' => 'api'],function ($api){
    $api -> group(['namespace' => 'App\Http\Controllers\NewWeb','middleware' => 'test.user.auth'],function ($api){

        /**
         * 测试页面登录
         */
        $api -> post('/testlogin','TestLoginController@login');

        /**
         * 测试刷新token
         */
//        $api -> post('test/testrefreshtoken','TestLoginController@refresh');
//        $api -> post('test/xxoo','TestLoginController@xxoo');



        // 验证管理员的登录信息
        $api->group(['prefix'=>'test','middleware' => ['jwt.auth']],function($api) {
//        $api->group(['prefix'=>'test','middleware' => ['jwt.auth']],function($api) {

            /**
             * 七牛云的路由
             */
            $api->group(['prefix' => 'cloudStorage'],function($api){
                $api->post('token','CloudStorageController@token');
            });
            /**
             * 网站前台测试页面
             */
            $api -> post('index','Test\ProductionController@index');


            /**
             * 作品页面
             */
            $api -> post('production','Test\ProductionController@Production');

            /**
             * 作品页面播放量接口
             */
            $api ->post('getcount','Test\ProductionController@getCount');

            /**
             * 作品页面状态接口
             */
            $api ->post('getstatus','Test\ProductionController@getStatus');

            /**
             * 电影节页面
             */
            $api -> post('/filmfest','Test\FilmFestController@index');

            /**
             * 作品上传页面
             */
            $api -> post('/up','Test\ProductionController@Up');

            /**
             * 作品执行上传
             */
            $api -> post('doup','Test\ProductionController@doUp');

            /**
             * 测试上传
             */
            $api -> post('testdoup','Test\ProductionController@testDoUp');


            /**
             * 加密
             */
            $api -> post('drm','Test\ProductionController@drm');

            /**
             * 删除作品
             */
            $api -> post('production/delete','Test\ProductionController@delete');

            /**
             * 获得隐私权限
             */
            $api -> post('production/privacy','Test\ProductionController@privacy');

            /**
             * 上传类别
             */
            $api -> post('production/channel','Test\ProductionController@channel');

            /**
             * 点击公有获得的数据
             */
            $api -> post('public','Test\ProductionController@publicStataus');

            $api -> group(['prefix'=>'user'],function ($api){

                /**
                 * 用户首页
                 */
                $api -> post('index','User\UserController@index');

                /**
                 * 获得分类
                 */
                $api -> post('gettype','User\UserCommonController@type');

                /**
                 * 获得布局
                 */
                $api -> post('getlayout','User\UserCommonController@layout');

                /**
                 * 获得排序方法
                 */
                $api -> post('getorderby','User\UserCommonController@orderby_function');

                /**
                 * 主页动态接口
                 */
                $api -> post('tweet','User\UserController@tweet');

                /**
                 * 主页我的视频接口
                 */
                $api -> post('myvideo','User\UserController@myVideo');

                /**
                 * 主页发现接口
                 */
                $api -> post('discover','User\UserController@discover');

                /**
                 * 主页竞赛接口
                 */
                $api -> post('match','User\UserController@match');

            });

            /**
             * 请求播放视频接口
             */
            $api -> post('play','Play\PlayController@play');

            /**
             * 请求分辨率
             */
            $api -> post('getresolutionratio','Play\PlayController@resolution_ratio');


            $api -> group(['prefix'=>'filmfestadmin'],function($api){

                /**
                 * 分析页
                 */
                $api -> post('index','User\FilmfestController@index');

                /**
                 * 表格数据
                 */
                $api -> post('index/table','User\FilmfestController@table');

                /**
                 * 大学统计
                 */
                $api -> post('index/university/top','User\FilmfestController@universityTop');
                $api -> post('index/university','User\FilmfestController@university');

                /**
                 * 报名
                 */
                $api -> group(['prefix'=>'application'],function ($api){

                    $api -> post('page_one','Application\ApplicationController@pageOne');
                    $api -> post('page_two','Application\ApplicationController@pageTwo');
                    $api -> post('page_three','Application\ApplicationController@pageThree');
                    $api -> post('page_four','Application\ApplicationController@pageFour');
                    $api -> post('page_submit','Application\ApplicationController@pageSubmit');

                });

            });


        });
//        $api->get('users/check','User\FilmfestController@xxoo');
    });

});


/**
 * 后端改成用react框架后的新接口路由 20170910
 */
$api -> version('v1', ['namespace' => 'App\Http\Controllers\NewAdmin'], function($api) {

    $api->group(['prefix' => 'admins'], function ($api) {


        // 这个中间件主要更改验证的表的设置为administrator表
        $api->group(['middleware' => ['jwt.api.auth']],function($api) {

            // TODO 测试登录接口
            $api -> post('/sign','SignController@sign');

            // 刷新token 方式1
//            $api->group(['middleware' => ['jwt.refresh']], function ($api) {
//
//                $api->post('/token/refresh', 'SignController@refresh');
//            });

            // 刷新token 方式2
            $api->post('/token/refresh', 'SignController@refresh');

             // 验证管理员的登录信息
            $api->group(['middleware' => ['jwt.auth']],function($api) {

                /**
                 * 七牛云的路由
                 */
                $api->group(['prefix' => 'cloudStorage'],function($api){
                    $api->post('token','CloudStorageController@token');

                    $api->get('private/download/url','CloudStorageController@privateDownloadUrl');

                });

                /**
                 * 管理信息
                 */
                $api->post('/manage', 'ManageController@manage');

                /**
                 * 将发布地址存入数据库
                 */
                $api->post('/saveaddress','SaveAddressController@saveAddress');

                /**
                 * 站务
                 */
                $api -> group(['prefix'=>'office'],function ($api){

                    /**
                     * 上传用户端index配置文件
                     */
                    $api -> post('up/user/index','Office\WebIndexFileController@upUserIndex');

                    /**
                     * 上传管理员端index配置文件
                     */
                    $api -> post('up/admin/index','Office\WebIndexFileController@upAdminIndex');

                });

                /**
                 * 内容 TODO 新版接口  视频部分
                 */
                $api->group(['prefix' => 'video'], function ($api) {

                    /**
                     * 视频信息
                     */
                    $api->post('/index', 'VideosController@index');

                    /**
                     * 视频详情
                     */
                    $api->post('/details/{id}', 'VideosController@details')
                        -> where('id', '[0-9]+');

                    /**
                     * 推荐信息
                     */
                    $api->post('/recommend-info', 'VideosController@recommendInfo');

                    /**
                     * 推荐处理
                     */
                    $api->post('/recommend-dispose', 'VideosController@recommendDispose');

                    /**
                     * 屏蔽原因选择
                     */
                    $api->post('/forbid-reasons', 'VideosController@forbidReasons');

                    /**
                     * 屏蔽原因操作
                     */
                    $api->post('/forbid-done', 'VideosController@forbidDone');

                    /**
                     * 删除操作
                     */
                    $api->post('/delete', 'VideosController@delete');

                    /**
                     * 动态评论
                     */
                    $api->post('/reply', 'VideosController@reply');

                    /**
                     * 荣誉记录
                     */
                    $api->post('/trophy', 'VideosController@trophy');

                    /**
                     * 操作记录
                     */
                    $api->post('/check', 'VideosController@check');

                    /**
                     * 公共类别
                     */
                    $api->group(['prefix'=>'common'],function ($api){

                        /**
                         * 分类
                         */
                        $api->post('/gettype','Video\CommonController@getType');

                        /**
                         * 多久之内
                         */
                        $api->post('/gettime','Video\CommonController@getTime');

                        /**
                         * 操作员
                         */
                        $api->post('/operator','Video\CommonController@operator');

                        /**
                         * 时长
                         */
                        $api->post('/getduration','Video\CommonController@getDuration');

                        /**
                         * 播放量
                         */
                        $api->post('/playnum','Video\CommonController@playNum');
                    });

                });

                /**
                 * 内容  热搜词管理9
                 */
                $api->group(['prefix' => 'hotsearch'], function ($api){

                    /**
                     * 热搜详情
                     */
                    $api->post('/index','SearchController@index');

                });


                /**
                 * 素材
                 */
                $api->group(['prefix' => 'fodder'],function($api){

                    /**
                     * 平台看板
                     */
                   $api->post('/index','FodderController@index');

                   /**
                    * 发布片段-基本信息添加
                    */

                   $api->post('/issue/fragment/base','FodderController@isserFragmentBase');

                    /**
                     * 发布片段-添加分类
                     */
                    $api->post('/issue/fragment/addtype','FodderController@isserFragmentAddtype');

                    /**
                     * 发布片段-添加画面比例
                     */
                    $api->post('/issue/fragment/addaspectradio','FodderController@issueFragmentAddAspectRadio');

                    /**
                     * 发布片段-添加资费
                     */
                    $api->post('/issue/fragment/addintergal','FodderController@issueFragmentAddIntergal');

                    /**
                     * 发布片段-添加地址-国家
                     */
                    $api->post('/issue/fragment/addresscountry','AddAddressController@addresscountry');

                    /**
                     * 发布片段-添加地址-省份
                     */
                    $api->post('/issue/fragment/addressprovince','AddAddressController@addressprovince');

                    /**
                     * 发布片段-添加地址-城市（上级为省份）
                     */
                    $api->post('/issue/fragment/addresscity','AddAddressController@addresscity');

                    /**
                     * 发布片段-添加地址-城市（上级为国家）
                     */
                    $api->post('/issue/fragment/addressstate','AddAddressController@addressstate');

                    /**
                     * 发布片段-添加地址-县区
                     */
                    $api->post('/issue/fragment/addresscounty','AddAddressController@addresscounty');

                    /**
                     * 发布片段-发布
                     */
                    $api->post('/issue/fragment/issue','FodderController@issue');



                    /**
                     * 执行发布
                     */
                    $api->post('/issue/fragment/doissue','FodderController@doissue');

                    /**
                     * 取消发布
                     */
                    $api->post('/issue/fragment/cancel','FodderController@cancel');

                    /**
                     * 清空发布
                     */
                    $api->post('/issue/fragment/cancel','FodderController@clear');

                    /**
                     * 片段   未写接口文档
                     */
                    $api->group(['prefix'=>'fragment'],function ($api){

                        /**
                         * 片段首页
                         */
                        $api->post('/index','Fodder\FragmentController@index');

                        /**
                         * 获取分类
                         */
                        $api->post('/gettype','Fodder\FragmentController@gettype');

                        /**
                         * 获取操作员
                         */
                        $api->post('/getoperator','Fodder\FragmentController@getOperator');

                        /**
                         * 获取发布时间
                         */
                        $api->post('/gettime','Fodder\FragmentController@getTime');

                        /**
                         * 获取时长
                         */
                        $api->post('/getduration','Fodder\FragmentController@getDuration');

                        /**
                         * 获取下载量
                         */
                        $api->post('/getcount','Fodder\FragmentController@getCount');

                        /**
                         * 变更推荐状态
                         */
                        $api->post('/dorecommend','Fodder\FragmentController@doRecommend');

                        /**
                         * 变更推荐状态
                         */
                        $api->post('/cancelrecommend','Fodder\FragmentController@cancelRecommend');

                        /**
                         * 变更置顶状态
                         */
                        $api->post('/doishot','Fodder\FragmentController@doIsHot');

                        /**
                         * 变更置顶状态
                         */
                        $api->post('/cancelishot','Fodder\FragmentController@cancelIsHot');

                        /**
                         * 设置置顶过期时间
                         */
                        $api->post('/ishottime','Fodder\FragmentController@isHotTime');
                        /**
                         * 进行屏蔽
                         */
                        $api->post('/doshield','Fodder\FragmentController@doShield');

                        /**
                         * 推荐位片段
                         */
                        $api->post('/recommend','Fodder\FragmentController@recommend');

                        /**
                         * 片段分类管理
                         */
                        $api->post('/type','Fodder\FragmentController@type');

                        /**
                         * 分类排序向上
                         */
                        $api->post('/up','Fodder\FragmentController@up');

                        /**
                         * 分类排序向下
                         */
                        $api->post('/down','Fodder\FragmentController@down');

                        /**
                         * 变更是否分类启用停用状态
                         */
                        $api->post('/stop','Fodder\FragmentController@stop');

                        /**
                         * 变更是否分类启用停用状态
                         */
                        $api->post('/start','Fodder\FragmentController@start');

                        /**
                         * 屏蔽仓
                         */
                        $api->post('/shieldwarehouse','Fodder\FragmentController@shieldWareHouse');

                        /**
                         * 取消屏蔽
                         */
                        $api->post('/cancelshield','Fodder\FragmentController@cancelShield');

                        /**
                         * 删除片段
                         */
                        $api->post('/delete','Fodder\FragmentController@delete');

                        /**
                         * 变更分类
                         */
                        $api->post('/changetype','Fodder\FragmentController@changeType');


                    });


                   /**
                    * 模板
                    */
                   $api->group(['prefix' => 'template'],function ($api){

                       /**
                        * 分类-添加片段分类
                        */
                        $api->post('/add/type','TemplageController@addType');

                       /**
                        * 首页
                        */
                        $api->post('/index','Template\TemplateController@index');

                        /**
                         * 推荐页
                         */
                        $api->post('/recommend','Template\TemplateController@recommend');

                        /**
                         * 热门页
                         */
                        $api->post('/hot','Template\TemplateController@hot');

                        /**
                         * 屏蔽页
                         */
                        $api->post('/shield','Template\TemplateController@shield');

                        /**
                         * 分类页
                         */
                        $api->post('/type','Template\TemplateController@type');

                        /**
                         * 添加新分类
                         */
                        $api->post('/addtype','Template\TemplateController@addType');

                       /**
                        * 上传模板页面
                        */
                       $api -> post('/up','Template\TemplateController@up');

                       /**
                        * 执行上传
                        */
                       $api -> post('/doup','Template\TemplateController@doUp');

                        /**
                         * 操作及公共内容
                         */
                        $api->group(['prefix'=>'common'],function ($api){
                            /**
                             * 分类条件
                             */
                            $api->post('/type','Template\TemplateCommonController@type');

                            /**
                             * 操作员条件
                             */
                            $api->post('/operator','Template\TemplateCommonController@operator');

                            /**
                             * 时间条件
                             */
                            $api->post('/time','Template\TemplateCommonController@time');

                            /**
                             * 时长条件
                             */
                            $api->post('/duration','Template\TemplateCommonController@duration');

                            /**
                             * 下载量条件
                             */
                            $api->post('/count','Template\TemplateCommonController@count');

                            /**
                             * 推荐操作
                             */
                            $api->post('/recommend','Template\TemplateCommonController@recommend');

                            /**
                             * 取消推荐
                             */
                            $api->post('/cancelrecommend','Template\TemplateCommonController@cancelRecommend');
                            /**
                             * 热门
                             */
                            $api->post('/hot','Template\TemplateCommonController@hot');
                            /**
                             * 取消热门
                             */
                            $api->post('/cancelhot','Template\TemplateCommonController@cancelHot');
                            /**
                             * 屏蔽
                             */
                            $api->post('/shield','Template\TemplateCommonController@shield');
                            /**
                             * 取消屏蔽
                             */
                            $api->post('/cancelshield','Template\TemplateCommonController@cancelShield');
                            /**
                             * 删除
                             */
                            $api->post('/delete','Template\TemplateCommonController@delete');
                            /**
                             * 向上
                             */
                            $api->post('/up','Template\TemplateCommonController@up');
                            /**
                             * 向下
                             */
                            $api->post('/down','Template\TemplateCommonController@down');
                            /**
                             * 停用
                             */
                            $api->post('/stop','Template\TemplateCommonController@stop');
                            /**
                             * 启用
                             */
                            $api->post('/start','Template\TemplateCommonController@start');

                            /**
                             * 进行分类
                             */
                            $api->post('/dotype','Template\TemplateCommonController@doType');


                        });



                   });


                   /**
                    * 混合资源模块
                    */
                   $api->group(['prefix'=>'mixresource'],function ($api){

                       /**
                        * 上传页面
                        */
                       $api->post('/issue','MixResource\MixResourceController@issue');

                       /**
                        * 发布信息展示页面
                        */
                       $api->post('/issueplay','MixResource\MixResourceController@issuePlay');

                       /**
                        * 执行发布
                        */
                       $api->post('doissue','MixResource\MixResourceController@doIssue');

                       /**
                        * 取消发布
                        */
                       $api->post('/cancelissue','MixResource\MixResourceController@cancelIssue');
                       /**
                        * 清空发布
                        */
                       $api->post('/clear','MixResource\MixResourceController@clear');

                       /**
                        * 混合主页
                        */
                       $api->post('/index','MixResource\MixResourceController@index');

                       /**
                        * 混合推荐页
                        */
                       $api->post('/recommend','MixResource\MixResourceController@recommend');

                       /**
                        * 屏蔽仓
                        */
                       $api->post('/shield','MixResource\MixResourceController@shield');

                       /**
                        * 分类
                        */
                       $api->post('/type','MixResource\MixResourceController@type');

                       /**
                        * 添加分类
                        */
                       $api->post('/addtype','MixResource\MixResourceController@addType');

                       /**
                        * 公共功能与操作
                        */
                       $api->group(['prefix'=>'common'],function ($api){

                           /**
                            * 获取分类条件
                            */
                           $api->post('/type','MixResource\CommonController@type');

                           /**
                            * 获取资费
                            */
                           $api->post('/downloadcost','MixResource\CommonController@downloadCost');

                           /**
                            * 时间条件
                            */
                           $api->post('/time','MixResource\CommonController@time');

                           /**
                            * 时长条件
                            */
                           $api->post('/duration','MixResource\CommonController@duration');

                           /**
                            * 下载量条件
                            */
                           $api->post('/count','MixResource\CommonController@count');

                           /**
                            * 推荐
                            */
                           $api->post('/dorecommend','MixResource\CommonController@doRecommend');

                           /**
                            * 取消推荐
                            */
                           $api->post('/cancelrecommend','MixResource\CommonController@cancelRecommend');

                           /**
                            * 进行分类
                            */
                           $api->post('/dotype','MixResource\CommonController@doType');

                           /**
                            * 屏蔽
                            */
                           $api->post('/doshield','MixResource\CommonController@doShield');

                           /**
                            * 取消屏蔽
                            */
                           $api->post('/cancelshiled','MixResource\CommonController@cancelShield');

                           /**
                            * 向上
                            */
                           $api->post('/up','MixResource\CommonController@up');

                           /**
                            * 向下
                            */
                           $api->post('/down','MixResource\CommonController@down');

                           /**
                            * 启用
                            */
                           $api->post('/start','MixResource\CommonController@start');

                           /**
                            * 停用
                            */
                           $api->post('/stop','MixResource\CommonController@stop');

                           /**
                            * 删除类别
                            */
                           $api->post('/type/delete','MixResource\CommonController@typeDelete');

                           /**
                            * 删除资源
                            */
                           $api->post('/resource/delete','MixResource\CommonController@resourceDelete');

                           /**
                            * 混合纹理类别
                            */
                           $api->post('/mixtexture','MixResource\CommonController@mixTexture');

                       });
                   });


                });

                /**
                 * 移动端
                 */
                $api->group(['prefix' => 'mobile'],function($api){

                    /**
                     * 滤镜
                     */

                    $api->group(['prefix' => 'filter'],function($api){

                        /**
                         * 滤镜主页
                         */
                        $api->post('/index','FilterController@index');

                        /**
                         * 变更是否推荐
                         */
                        $api->post('/changerecommend','FilterController@changerecommend');

                        /**
                         * 变更是否是上热门
                         */
                        $api->post('/changeishot','FilterController@changishot');

                        /**
                         * 进行屏蔽
                         */
                        $api->post('doshield','FilterController@doshield');

                        /**
                         * 推荐位滤镜
                         */
                        $api->post('/recommend','FilterController@recommend');

                        /**
                         * 滤镜分类
                         */
                        $api->post('/type','FilterController@type');

                        /**
                         * 分类排序向上
                         */
                        $api->post('/up','FilterController@up');

                        /**
                         * 分类排序向下
                         */
                        $api->post('/down','FilterController@down');

                        /**
                         * 变更是否停用该分类
                         */
                        $api->post('/changestop','FilterController@changestop');

                        /**
                         * 创建新分类
                         */
                        $api->post('/mktype','FilterController@makenewtype');

                        /**
                         * 搜索热点  未写
                         */
                        $api->post('/hotsearch','FilterController@hotsearch');

                        /**
                         * 发布滤镜页面
                         */
                        $api->post('/addfilter','FilterController@addfilter');

                        /**
                         * 获得滤镜资费情况
                         */
                        $api->post('/getintegral','FilterController@getIntegral');

                        /**
                         * 发布页面获得滤镜纹理混合分类
                         */
                        $api->post('/gettexturemixtype','FilterController@getTextureMixType');

                        /**
                         * 获取操作员
                         */
                        $api->post('/getoperator','FilterController@getOperator');

                        /**
                         * 获取发布时间
                         */
                        $api->post('/gettime','FilterController@getTime');

                        /**
                         * 获取下载量
                         */
                        $api->post('/getcount','FilterController@getCount');

                        /**

                         * 执行发布滤镜
                         */
                        $api->post('/doaddfilter','FilterController@doAddFilter');

                        /**
                         * 滤镜添加分类
                         */
                        $api->post('/addfiltertype','FilterController@addFilterType');

                        /**
                         * 屏蔽仓
                         */
                        $api->post('/shieldwarehouse','FilterController@shieldwarehouse');

                        /**
                         * 取消屏蔽
                         */
                        $api->post('/cancelshield','FilterController@cancelshield');

                        /**
                         * 删除滤镜
                         */
                        $api->post('/delete','FilterController@delete');

                        /**
                         * 设置置顶过期时间
                         */
                        $api->post('/ishottime','FilterController@isHotTime');

                        /**
                         * 变更分类
                         */
                        $api->post('/changetype','FilterController@changeType');

                    });
                });


                /**
                 * 用户
                 */
                $api->group(['prefix'=>'user'],function ($api){

                    /**
                     * 监控模块
                     */
                    $api->group(['prefix'=>'supervisory'],function ($api){


                        $api->post('/index','User\SupervisoryController@index');

                    });

                    /**
                     * 用户管理模块
                     */
                    $api->group(['prefix'=>'manage'],function ($api){
                        /**
                         * 首页
                         */
                        $api->post('/index','User\UserManageController@index');

                        /**
                         * 用户管理首页所用的角色类别
                         */
                        $api->post('/index/getVipLevel','User\UserManageController@getVipLevel');

                        /**
                         * 第三方用户页面
                         */
                        $api->post('/thirdparty','User\UserManageController@thirdparty');

                        /**
                         * 第三方用户页面类型条件
                         */
                        $api->post('/thirdparty/thirdtype','User\UserManageController@thirdType');

                        /**
                         * vip页面
                         */
                        $api->post('/vipuser','User\UserManageController@vipUser');

                        /**
                         * vip用户页面专用vip级别
                         */
                        $api->post('/vipuser/viplevel','User\UserManageController@vipLevel');

                        /**
                         * 机构页面
                         */
                        $api->post('/organization','User\UserManageController@organization');

                        /**
                         * 认证用户
                         */
                        $api->post('/verifyuser','User\UserManageController@verifyUser');

                        /**
                         * 创作者
                         */
                        $api->post('/creater','User\UserManageController@creater');

                        /**
                         * 审查者
                         */
                        $api->post('/investigate','User\UserManageController@investigate');

                        /**
                         * 精选用户界面
                         */
                        $api->post('/choiceness','User\UserManageController@choiceness');

                        /**
                         * 冻结用户界面
                         */
                        $api->post('/stop','User\UserManageController@stop');

                        /**
                         * 用户公共条件以及操作
                         */
                        $api->group(['prefix'=>'common'],function ($api){

                            /**
                             * 粉丝数量
                             */
                            $api->post('/getfansnum','User\UserCommonTypeController@getFansNum');

                            /**
                             * 播放数量
                             */
                            $api->post('/getplaycount','User\UserCommonTypeController@getPlayCount');

                            /**
                             * 作品数量
                             */
                            $api->post('/productionnum','User\UserCommonTypeController@productionNum');

                            /**
                             * 资产数量
                             */
                            $api->post('/integralnum','User\UserCommonTypeController@intergalNum');

                            /**
                             * 选择审核人
                             */
                            $api->post('/checker','User\UserCommonTypeController@checker');

                            /**
                             * 精选操作
                             */
                            $api->post('/dochoiceness','User\UserCommonTypeController@doChoiceness');

                            /**
                             * 升级操作
                             */
                            $api->post('/levelup','User\UserCommonTypeController@levelUp');

                            /**
                             * 冻结操作
                             */
                            $api->post('/dostop','User\UserCommonTypeController@doStop');

                            /**
                             * 取消精选操作
                             */
                            $api->post('/cancelchoiceness','User\UserCommonTypeController@cancelChoiceness');

                            /**
                             * 解冻操作
                             */
                            $api->post('/cancelstop','User\UserCommonTypeController@cancelStop');

                            /**
                             * 删除操作
                             */
                            $api->post('/delete','User\UserCommonTypeController@delete');

                        });
                    });

                });

            });
        });
    });
});

// 后台路由
Route::group(['prefix' => 'admin','namespace' => 'Admin','middleware' => ['web']],function(){

    /**
     * 后台登录页面
     */
    Route::get('/login','LoginController@login');

    /**
     * 用户登录
     */
    Route::post('/signin','LoginController@signIn');

    /**
     * 用户登出
     */
    Route::get('/logout','LoginController@logout');

    Route::group(['middleware' => 'auth.admin','as' => 'admin::'],function() {

        /**
         * 请求上传到七牛的token
         */
        Route::get('/up_token','Qiniu\QiniuController@token');

        /**
         * 主页面
         */
        Route::get('/dashboard','DashboardController@dashboard');

        /**
         * 个人用户界面（允许修改头像）
         */
        Route::get('/account','UserController@account');

        /**
         * 修改头像路由
         */
        Route::post('/account/avatar','UserController@avatarUpload');

        /**
         * 剪辑头像
         */
        Route::post('/account/avatar/crop','UserController@avatarCrop');
        /**
         * 获取上传的图片在页面中显示
         */
        Route::get('images/{id}/{filename}','ImageController@show');
        Route::get('images/{id}/temp/{filename}','ImageController@showTemp');
        /**
         * 页面路由
         */
        Route::get('/management/family/member',
            'Management\Family\MemberController@show')->name('management::family::member');

        Route::get('/management/family/setting',
            'Management\Family\SettingController@show')->name('management::family::setting');

        // 后台用户管理
        Route::group(['prefix'=>'user'],function(){

            //
            Route::group(['prefix'=>'list'],function(){

                Route::get('/local','WebUserController@local');
                Route::get('/oauth','WebUserController@oauth');
                Route::get('/delete','WebUserController@delete');
                Route::get('/localrecycle','WebUserController@localrecycle');
                Route::get('/oauthrecycle','WebUserController@oauthrecycle');
                Route::get('/statistics','WebUserController@statistics');
                Route::get('/add','WebUserController@add');
                Route::post('/insert','WebUserController@insert');
            });

            // 用户角色发布管理
            Route::group(['prefix' => 'role', 'namespace' => 'UserRole'], function() {

                Route::get('/index','UserRoleController@index');
                Route::get('/details','UserRoleController@details');
                Route::post('/update','UserRoleController@update');
                Route::post('/sort','UserRoleController@sort');
                Route::post('/type-insert','UserRoleController@typeInsert');
                Route::get('/type','UserRoleController@type');
                Route::get('/type-add','UserRoleController@typeAdd');
            });
        });

        // TODO 禁用闭包，影响缓存，应改成控制器
//        Route::get('/app/camera/UI',function(){
//            return view('admin/app/camera/UI');
//        })->name('APP::camera::UI');

        Route::get('/app/camera/blur',
            'APP\Camera\BlurController@index')->name('APP::camera::blur');

        /**
         * 广告 TODO 禁用闭包，影响缓存，应改成控制器
         */
//        Route::get('/advertisement/goobird',function(){
//            return view('admin/advertisement/goobird');
//        })->name('ad::goobird');
//
//        Route::get('/advertisement/user',function(){
//            return view('admin/advertisement/user');
//        })->name('ad::user');

        // 频道广告
        Route::group(['prefix' => 'advertisement','namespace' => 'Content'],function(){

            // 频道广告位管理
            Route::group(['prefix' => 'channel'], function(){

                Route::get('index','AdsController@index');
                Route::get('details','AdsController@details');
                Route::get('add','AdsController@add');
                Route::post('insert','AdsController@insert');
                Route::post('update','AdsController@update');
            });

            // 大家都在看广告位管理
            Route::group(['prefix' => 'view'], function(){

                Route::get('index','ViewController@index');
                Route::get('details','ViewController@details');
                Route::get('add','ViewController@add');
                Route::post('insert','ViewController@insert');
                Route::post('update','ViewController@update');
            });
        });

        // 创作首页
        Route::group(['prefix' => 'creation','namespace' => 'Creation'],function(){

            // 创作首页 封面管理
            Route::group(['prefix' => 'cover'], function(){

                Route::get('index','CoverController@index');
                Route::get('details','CoverController@details');
                Route::get('add','CoverController@add');
                Route::post('insert','CoverController@insert');
                Route::post('update','CoverController@update');
                Route::post('sort','CoverController@sort');
            });

        });

        /**
         * 举报
         */
        Route::group(['prefix' => 'complains', 'namespace' => 'Content'], function(){

            Route::get('index','ComplainsController@index');
            Route::get('details','ComplainsController@details');
            Route::post('update','ComplainsController@update');
        });

        /**
         * 评论
         */
        Route::group(['prefix' => 'reply', 'namespace' => 'Content'], function(){

            Route::get('index','ReplyController@index');
            Route::get('ajax','ReplyController@ajax');
        });

        /**
         * 用户的认证
         */
        Route::group(['prefix' => 'verify', 'namespace' => 'Verify'], function(){

            Route::get('index','VerifyController@index');
            Route::get('details','VerifyController@details');
            Route::post('update','VerifyController@update');
        });

        /**
         * 权限的管理
         */
        Route::group(['prefix' => 'role', 'namespace' => 'Role'], function(){

            Route::get('index','RoleGroupController@index');
            Route::get('add','RoleGroupController@add');
            Route::get('edit','RoleGroupController@edit');
            Route::get('details','RoleGroupController@details');
            Route::get('delete','RoleGroupController@delete');
            Route::post('insert','RoleGroupController@insert');
            Route::post('update','RoleGroupController@update');
        });

        /**
         * 路由的管理
         */
        Route::group(['prefix' => 'menu', 'namespace' => 'Menu'], function(){

            Route::get('index','MenuController@index');
            Route::get('add','MenuController@add');
            Route::get('edit','MenuController@edit');
            Route::get('delete','MenuController@delete');
            Route::post('insert','MenuController@insert');
            Route::post('update','MenuController@update');
        });

        /**
         * 用户需求详情的管理
         */
        Route::group(['prefix' => 'demand', 'namespace' => 'Demand'], function(){

            Route::get('index','UserDemandController@index');
            Route::get('details','UserDemandController@details');
            Route::post('update','UserDemandController@update');
        });

        /**
         * 用户租赁的管理
         */
        Route::group(['prefix' => 'lease', 'namespace' => 'Lease'], function(){

            Route::get('index','UserLeaseController@index');
            Route::get('details','UserLeaseController@details');
            Route::post('update','UserLeaseController@update');
        });

        /**
         * APP各类协议数据
         */
        Route::group(['prefix' => 'agreement', 'namespace' => 'Agreement'], function(){

            Route::get('index','AgreementController@index');
            Route::get('add','AgreementController@add');
            Route::get('edit','AgreementController@edit');
            Route::get('details','AgreementController@details');
            Route::post('insert','AgreementController@insert');
            Route::post('update','AgreementController@update');
        });

        /**
         * 上传文件管理
         */
        Route::group(['prefix' => 'file', 'namespace' => 'UploadFiles'], function(){

            Route::get('index','UploadFilesController@index');
            Route::get('add','UploadFilesController@add');
            Route::post('insert','UploadFilesController@insert');
            Route::post('delete','UploadFilesController@delete');
        });

        /**
         * 用户需求配置
         */
        Route::group(['prefix' => 'config'],function(){

            /**
             * 用户需求种类的管理
             */
            Route::group(['prefix' => 'demand', 'namespace' => 'Demand'], function(){

                Route::get('index','DemandJobController@index');
                Route::get('add','DemandJobController@add');
                Route::get('edit','DemandJobController@edit');
                Route::get('delete','DemandJobController@delete');
                Route::post('insert','DemandJobController@insert');
                Route::post('update','DemandJobController@update');
            });

            /**
             * 影片种类的管理
             */
            Route::group(['prefix' => 'film', 'namespace' => 'Demand'], function(){

                Route::get('index','FilmMenuController@index');
                Route::get('add','FilmMenuController@add');
                Route::get('edit','FilmMenuController@edit');
                Route::post('insert','FilmMenuController@insert');
                Route::post('update','FilmMenuController@update');
                Route::post('sort','FilmMenuController@sort');
            });

            /**
             * 租赁商品类型的管理
             */
            Route::group(['prefix' => 'lease', 'namespace' => 'Lease'], function(){

                Route::get('index','LeaseTypeController@index');
                Route::get('add','LeaseTypeController@add');
                Route::get('edit','LeaseTypeController@edit');
                Route::get('delete','LeaseTypeController@delete');
                Route::post('insert','LeaseTypeController@insert');
                Route::post('update','LeaseTypeController@update');
            });
        });

        /**
         * 后台操作日志
         */
        Route::group(['prefix' => 'log', 'namespace' => 'Log'], function(){

            Route::get('tweet','LogController@tweet');
            Route::get('user','LogController@user');
            Route::get('topic','LogController@topic');
            Route::get('activity','LogController@activity');
            Route::get('reply','LogController@reply');
            Route::get('maintain','LogController@maintain');
        });

        /**
         * 视频审批/屏蔽查询/ajax处理等 隐式控制器  临时测试
         */
        Route::group(['prefix' => 'video', 'namespace' => 'Video'], function(){

            Route::get('index','VideoManageController@index');
            Route::get('check','VideoManageController@check');
            Route::get('create','VideoManageController@create');
            Route::get('insert','VideoManageController@insert');
            Route::get('edit','VideoManageController@edit');
            Route::get('recycle','VideoManageController@recycle');
            Route::get('amount','VideoManageController@amount');
            Route::post('insert','VideoManageController@insert');
            Route::post('apply','VideoManageController@apply');
            Route::post('update','VideoManageController@update');
        });

        /**
         * 网站信息维护
         */
        Route::group(['prefix' => 'maintain', 'namespace' => 'SitesMaintain'], function(){

            Route::get('index','SitesMaintainController@index');
            Route::get('add','SitesMaintainController@add');
            Route::get('details','SitesMaintainController@details');
            Route::get('edit','SitesMaintainController@edit');
            Route::get('maintain','SitesMaintainController@maintain');
            Route::get('cache','SitesMaintainController@cache');
            Route::post('insert','SitesMaintainController@insert');
            Route::post('update','SitesMaintainController@update');
            Route::post('apply','SitesMaintainController@apply');
            Route::post('status','SitesMaintainController@status');
            Route::post('cache-flush','SitesMaintainController@cacheFlush');
        });

        /**
         * 发现页面院线管理
         */
        Route::group(['prefix' => 'cinema','as' => 'cinema::','namespace' => 'Cinema'],function(){

            // 院线管理
            Route::group(['prefix' => 'manage'], function(){

                Route::get('index','CinemaController@index');
                Route::get('add','CinemaController@add');
                Route::get('details','CinemaController@details');
                Route::get('edit','CinemaController@edit');
                Route::post('insert','CinemaController@insert');
                Route::post('update','CinemaController@update');
            });

            // 院线图片管理
            Route::group(['prefix' => 'picture'], function(){

                Route::get('index','CinemaPictureController@index');
                Route::get('add','CinemaPictureController@add');
                Route::get('details','CinemaPictureController@details');
                Route::get('edit','CinemaPictureController@edit');
                Route::post('insert','CinemaPictureController@insert');
                Route::post('update','CinemaPictureController@update');
            });
        });


        /**
         * 内容
         */
        Route::group(['prefix' => 'content','as' => 'content::','namespace' => 'Content'],function(){

            /**
             * 视频
             */
            Route::resource('video','VideoController' , [
                'names' =>
                    [
                        'create' => 'video',
                        'store'  => 'video',
                        'index'  => 'video',
                        'update' => 'video',
                        'show'   => 'video',
                        'edit'   => 'video'
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'video' => 'id'
                ]
            ]);

            /**
             * 视频审批/屏蔽查询/ajax处理等 隐式控制器
             */
            Route::group(['prefix' => 'ajax'], function(){

                Route::get('video','AjaxController@video');
                Route::get('check','AjaxController@check');
                Route::get('forbid','AjaxController@forbid');
                Route::get('apply','AjaxController@apply');
                Route::post('choose','AjaxController@choose');
            });

            /**
             * 每日推送视频管理
             */
            Route::resource('push','PushController' , [
                'names' =>
                    [
                        'create' => 'push',
                        'store'  => 'push',
                        'index'  => 'push',
                        'update' => 'push',
                        'show'   => 'push',
                        'edit'   => 'push'
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'push' => 'id'
                ]
            ]);


            /**
             * 影集
             */
            Route::resource('photo_album','PhotoAlbumController' , [
                'names' =>
                    [
                        'create' => 'photo_album',
                        'store'  => 'photo_album',
                        'index'  => 'photo_album',
                        'update' => 'photo_album',
                        'show'   => 'photo_album',
                        'edit'   => 'photo_album',
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'video' => 'id'
                ]
            ]);

            /**
             * 话题
             */
            Route::resource('topic','TopicController' , [
                'names' =>
                    [
                        'create' => 'topic',
                        'store'  => 'topic',
                        'index'  => 'topic',
                        'update' => 'topic',
                        'show'   => 'topic',
                        'edit'   => 'topic'
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'topic' => 'id'
                ]
            ]);
            Route::get('topic/{id}/recommend_channel','TopicController@recommendChannel');

            /**
             * 活动
             */
            Route::resource('activity','ActivityController' , [
                'names' =>
                    [
                        'create' => 'topic',
                        'store'  => 'topic',
                        'index'  => 'topic',
                        'update' => 'topic',
                        'show'   => 'topic',
                        'edit'   => 'topic'
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'activity' => 'id'
                ]
            ]);
            Route::get('activity/{id}/recommend_channel','ActivityController@recommendChannel');
            Route::get('activity/{id}/tweets','ActivityController@tweets');
            Route::post('activities/insert','ActivityController@insert');
            Route::post('activities/{id}/recommend','ActivityController@recommend');


            /**
             * 赛事奖金的分配设置
             */
            Route::group(['prefix' => 'competition'], function(){

                Route::get('index', 'CompetitionController@index');
                Route::get('edit', 'CompetitionController@edit');
                Route::post('update', 'CompetitionController@update');
            });

            /**
             * 搜索热词管理
             */
            Route::resource('search','SearchController' , [
                'names' =>
                    [
                        'create' => 'search',
                        'store'  => 'search',
                        'index'  => 'search',
                        'update' => 'search',
                        'show'   => 'search',
                        'edit'   => 'search'
                    ],
                'except' => ['destroy'],
                'parameters' => [
                    'search' => 'id'
                ]
            ]);

            /**
             * 标签
             */
            Route::resource('label','LabelController' , [
                'names' =>
                    [
                        'create' => 'label',
                        'store'  => 'label',
                        'index'  => 'label',
                        'update' => 'label',
                        'show'   => 'label'
                    ],
                'except' => ['destroy','edit'],
                'parameters' => [
                    'label' => 'id'
                ]
            ]);
        });

        /**
         * 频道排序修改
         */
        Route::group(['prefix' => 'channel', 'namespace' => 'System'], function(){

            Route::get('sort','SortController@sort');
        });

        Route::group(['prefix' => 'system_management','as' => 'system_management::'],function(){
            /**
             * 频道
             */
            Route::resource('channel','System\ChannelController' ,
                [
                    'names' =>
                        [
                            'create' => 'channel',
                            'store'  => 'channel',
                            'index'  => 'channel',
                            'update' => 'channel',
                            'show'   => 'channel',
                            'edit'   => 'channel'
                        ],
                    'except' => ['destroy'],
                    'parameters' => [
                        'channel' => 'id'
                    ]
                ]);

            /**
             * 奖杯
             */
            Route::resource('trophy','System\TrophyController' ,
                [
                    'names' =>
                        [
                            'create' => 'trophy',
                            'store'  => 'trophy',
                            'index'  => 'trophy',
                            'update' => 'trophy',
                            'show'   => 'trophy',
                            'edit'   => 'trophy'
                        ],
                    'except' => ['destroy'],
                    'parameters' => [
                        'trophy' => 'id'
                    ]
                ]);
        });
        /**
         * 部门 路由
         */
        Route::group(['prefix' => 'management/department',
            'as' => 'management::family::setting'],function(){
            /**
             * 部门添加页面
             */
            Route::get('/','DepartmentController@addPage');

            /**
             * 部门添加操作
             */
            Route::post('/','DepartmentController@add');

            /**
             *  部门通过审核操作
             */
            Route::post('/review/{id}','DepartmentController@review');

            /**
             * 部门审核不通过，删除部门操作
             */
            Route::post('/delete/{id}','DepartmentController@delete');

            /**
             * 启动部门操作
             */
            Route::post('/enable/{id}','DepartmentController@enable');

            /**
             * 停用部门操作
             */
            Route::post('/disable/{id}','DepartmentController@disable');

            /**
             * 编辑部门页面
             */
            Route::get('/edit/{id}','DepartmentController@editPage');

            /**
             * 提交编辑的部门
             */
            Route::post('/edit/{id}','DepartmentController@editByID');
        });


        /**
         * 职位 路由
         */
        Route::group(['prefix' => 'management/position',
            'as' =>'management::family::setting'],function(){
            /**
             * 职位添加页面
             */
            Route::get('/','PositionController@addPage');

            /**
             * 职位添加提交操作
             */
            Route::post('/','PositionController@add');

            /**
             * 职位审核通过操作
             */
            Route::post('/review/{id}','PositionController@review');

            /**
             * 职位审核不通过，删除操作
             */
            Route::post('/delete/{id}','PositionController@delete');

            /**
             * 职位启用操作
             */
            Route::post('/enable/{id}','PositionController@enable');

            /**
             * 职位停用操作
             */
            Route::post('/disable/{id}','PositionController@disable');

            /**
             * 职位编辑页面
             */
            Route::get('/edit/{id}','PositionController@editPage');

            /**
             * 职位编辑提交操作
             */
            Route::post('/edit/{id}','PositionController@editByID');
        });


        /**
         * 管理员 路由
         */
        Route::group(['prefix' => 'management/administrator',
            'as' => 'management::family::member'],function(){
            /**
             * 管理员添加页面
             */
            Route::get('/','UserController@addPage');

            /**
             * 管理员添加提交操作
             */
            Route::post('/','UserController@add');

            /**
             * 管理员停用提交操作
             */
            Route::post('/disabled/{id}','UserController@disabled');

            /**
             * 管理员启用提交操作
             */
            Route::post('/enabled/{id}','UserController@enabled');

            /**
             * 查看更多管理员信息
             */
            Route::get('/view-more/{id}','UserController@viewMore');

            /**
             * 编辑管理员页面
             */
            Route::get('/edit/{id}','UserController@editPage');

            /**
             * 编辑管理员提交操作
             */
            Route::post('/edit/{id}','UserController@editByID');
        });


        /**
         * APP分类页面路由
         */
        Route::group(['prefix' => 'app','namespace' =>  'APP'],function(){
            /**
             * 相机相关路由
             */
            Route::group(['prefix' => 'camera','namespace' => 'Camera'],function(){
                /**
                 * 滤镜相关路由
                 */
                Route::group(['prefix' => 'blur','as' => 'APP::camera::blur'],function(){
                    Route::get('/{id}','BlurController@show')
                        ->where('id','[0-9]+');

                    Route::post('/delete/{id}','BlurController@delete');

                    Route::post('/enable/{id}','BlurController@enable');

                    Route::post('/disable/{id}','BlurController@disable');

                    /**
                     * 滤镜类型相关路由
                     */
                    Route::group(['prefix' => 'class'],function(){
                        /**
                         * 添加滤镜类型画面
                         */
                        Route::get('/','BlurClassController@addClass');

                        /**
                         * 添加滤镜类型请求
                         */
                        Route::post('/','BlurClassController@add');

                        /**
                         * 滤镜 类型管理页面
                         */
                        Route::get('management','BlurClassController@classShow');

                        Route::post('disabled/{id}','BlurClassController@disabled');

                        Route::post('enabled/{id}','BlurClassController@enabled');

                        Route::get('edit/{id}','BlurClassController@editShow');

                        Route::post('edit/{id}','BlurClassController@edit');
                    });

                    /**
                     * 添加滤镜 路由 分步骤 由多个ajax完成
                     */
                    Route::group(['prefix' => 'add','namespace' => 'Blur'],function(){
                        Route::get('/','AddController@index');

                        Route::post('/blur_class','AddController@blurClass');

                        Route::post('/GPUImage','AddController@GPUImage');

                        Route::post('/gravity_image','AddController@gravityImage');

                        Route::post('/gravity_image/delete','AddController@delGravityImage');

                        Route::post('/face_image','AddController@faceImage');

                        Route::post('/face_image/delete','AddController@delFaceImage');

                        Route::post('/dynamic_image','AddController@dynamicImage');

                        Route::post('/dynamic_image/delete','AddController@delDynamicImage');

                        Route::post('/background_image','AddController@backgroundImage');

                        Route::post('/background_image/delete','AddController@delBackgroundImage');

                        Route::get('/background_image','AddController@backgroundCount');

                        Route::post('/','AddController@store');
                    });
                });
            });
        });

        /**
         * APP端视频编辑
         */
        Route::group(['prefix' => 'make','namespace' =>  'APP\Make'],function(){

            /**
             * 音频管理
             */
            Route::group(['prefix' => 'audio'],function(){

                // 音乐目录管理
                Route::group(['prefix' => 'folder'], function(){

                    Route::get('index','MakeAudioFolderController@index');
                    Route::get('add','MakeAudioFolderController@add');
                    Route::get('edit','MakeAudioFolderController@edit');
                    Route::post('sort','MakeAudioFolderController@sort');
                    Route::post('insert','MakeAudioFolderController@insert');
                    Route::post('update','MakeAudioFolderController@update');
                });

                // 音乐文件管理
                Route::group(['prefix' => 'file'], function(){

                    Route::get('index','MakeAudioFileController@index');
                    Route::get('add','MakeAudioFileController@add');
                    Route::post('insert','MakeAudioFileController@insert');
                    Route::post('sort','MakeAudioFileController@sort');
                });

                // 音效目录管理
                Route::group(['prefix' => 'effect/folder'], function(){

                    Route::get('index','MakeAudioEffectFolderController@index');
                    Route::get('add','MakeAudioEffectFolderController@add');
                    Route::get('edit','MakeAudioEffectFolderController@edit');
                    Route::post('sort','MakeAudioEffectFolderController@sort');
                    Route::post('insert','MakeAudioEffectFolderController@insert');
                    Route::post('update','MakeAudioEffectFolderController@update');
                });

                // 音效文件管理
                Route::group(['prefix' => 'effect/file'], function(){

                    Route::get('index','MakeAudioEffectFileController@index');
                    Route::get('add','MakeAudioEffectFileController@add');
                    Route::post('insert','MakeAudioEffectFileController@insert');
                    Route::post('sort','MakeAudioEffectFileController@sort');
                });
            });

            /**
             * 贴图管理
             */
            Route::group(['prefix' => 'chartlet'],function(){

                // 目录管理
                Route::group(['prefix' => 'folder'], function(){

                    Route::get('index','MakeChartletFolderController@index');
                    Route::get('add','MakeChartletFolderController@add');
                    Route::get('edit','MakeChartletFolderController@edit');
                    Route::post('sort','MakeChartletFolderController@sort');
                    Route::post('insert','MakeChartletFolderController@insert');
                    Route::post('update','MakeChartletFolderController@update');
                });

                // 文件管理
                Route::group(['prefix' => 'file'], function(){

                    Route::get('index','MakeChartletFileController@index');
                    Route::get('add','MakeChartletFileController@add');
                    Route::post('insert','MakeChartletFileController@insert');
                    Route::post('sort','MakeChartletFileController@sort');
                });
            });

            /**
             * 效果管理
             */
            Route::group(['prefix' => 'effects'],function(){

                // 目录管理
                Route::group(['prefix' => 'folder'], function(){

                    Route::get('index','MakeEffectsFolderController@index');
                    Route::get('add','MakeEffectsFolderController@add');
                    Route::get('edit','MakeEffectsFolderController@edit');
                    Route::post('sort','MakeEffectsFolderController@sort');
                    Route::post('insert','MakeEffectsFolderController@insert');
                    Route::post('update','MakeEffectsFolderController@update');
                });

                // 文件管理
                Route::group(['prefix' => 'file'], function(){

                    Route::get('index','MakeEffectsFileController@index');
                    Route::get('add','MakeEffectsFileController@add');
                    Route::post('insert','MakeEffectsFileController@insert');
                    Route::post('sort','MakeEffectsFileController@sort');
                });
            });

            /**
             * 滤镜管理
             */
            Route::group(['prefix' => 'filter'],function(){

                // 目录管理
                Route::group(['prefix' => 'folder'], function(){

                    Route::get('index','MakeFilterFolderController@index');
                    Route::get('add','MakeFilterFolderController@add');
                    Route::get('edit','MakeFilterFolderController@edit');
                    Route::post('sort','MakeFilterFolderController@sort');
                    Route::post('insert','MakeFilterFolderController@insert');
                    Route::post('update','MakeFilterFolderController@update');
                });

                // 文件管理
                Route::group(['prefix' => 'file'], function(){

                    Route::get('index','MakeFilterFileController@index');
                    Route::get('add','MakeFilterFileController@add');
                    Route::post('insert','MakeFilterFileController@insert');
                    Route::post('sort','MakeFilterFileController@sort');
                });
            });

            /**
             * 字体
             */
            Route::group(['prefix'=>'font/file'],function(){

                // 文件管理
                Route::get('index','MakeFontFileController@index');
                Route::get('add','MakeFontFileController@add');
                Route::post('insert','MakeFontFileController@insert');
                Route::post('sort','MakeFontFileController@sort');
            });
        });

        /**
         *  以下为ajax请求
         */
        Route::get('department/{id}','DepartmentController@show');

        Route::get('/userID','UserController@checkUserID');

    });

});


