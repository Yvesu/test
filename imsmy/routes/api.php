<?php

/**
 * Dingo Api
 * 仅使用Dingo API的路由才会生成API blueprint
 */
$api = app('Dingo\Api\Routing\Router');


$api->version(['v1','v2'],function($api){

    $api->group(['namespace' => 'App\Api\Controllers','middleware' => 'api'],function($api){

        // 检查app最新版本
        $api -> post('version/check', 'VersionController@check');
    });

});

// v1 version API: default
// 如果不是默认版本必须添加header项：Accept: application/vnd.YOUR_SUBTYPE.v1+json
//Todo api加密认证
$api->version(['v1'],function($api){

    $api->group(['namespace' => 'App\Api\Controllers','middleware' => 'api'],function($api){

        // 刷新token 直接通过 方式1
//        $api->group(['middleware' => ['jwt.refresh']], function ($api) {
//
//            $api->post('/token/refresh', 'AuthController@refresh');
//        });

        // 刷新token 方式2 app端难度比较低
        $api->post('/token/refresh', 'AuthController@refresh');

        /**
         * 七牛云的路由
         */
        $api->group(['prefix' => 'cloud-storage'],function($api){
            $api->post('token','CloudStorageController@token');

            $api->get('private-download-url','CloudStorageController@privateDownloadUrl');

            $api->delete('/file','CloudStorageController@deleteFile');

            $api->delete('/directory','CloudStorageController@deleteDirectory');
        });

        /**
         * 用户Users api
         */
        $api->group(['prefix' => 'users'],function($api){

            // 验证用户名是否已存在
            $api->get('check','AuthController@check');

            // 注册信息
            $api->post('register','AuthController@register');

            // 验证
            $api->post('authenticate','AuthController@authenticate');

            $api->post('sms-verify','AuthController@smsVerify');

            $api->post('reset-password','AuthController@passwordReset');

            $api->post('third-party-auth','AuthController@thirdPartyAuth');

            $api->group(['prefix' => '{id}','middleware' => ['jwt.auth','app.auth']],function($api){

                $api->get('me','AuthController@getAuthenticatedUser');

                $api->post('center','UserController@center');

                $api->post('avatar','UserController@avatar');

                $api->post('/phone/update','AuthController@phoneReset');

                // 用户认证
                $api->post('verify','UserVerifyController@index');

                $api->post('/update','UserController@update');

                // 查看用户设置
                $api->post('/setting','UserController@setting');

                // 修改用户设置
                $api->post('/set','UserController@set');

                // 视频制作，音频简单信息的接口，此处需要用户登录
                $api->group(['prefix' => '/audio'],function($api){

                    // 音乐下载
                    $api->post('/download','MakeAudioController@download');

                    // 音效下载
                    $api->post('/effect/download','MakeAudioEffectController@download');

                });

                // 第三方登录 绑定与解绑
                $api->group(['prefix' => '/third'],function($api){

                    // 绑定
                    $api->post('/add','AuthController@thirdRelatedAdd');

                    // 解绑
                    $api->post('/delete','AuthController@thirdRelatedDelete');

                });

                // xmpp聊天传送文件相关接口
                $api->group(['prefix' => '/xmpp'],function($api){

                    // 聊天时发送文件保存
                    $api->post('/upload','XmppController@upload');

                });

                // 用户账号管理
                $api->group(['prefix' => '/account'],function($api){

                    // 查询
                    $api->post('/manage','AuthController@accountManage');

                });

                // 视频制作，贴图信息的接口，此处需要用户登录
                $api->group(['prefix' => '/chartlet'],function($api){

                    // 下载
                    $api->post('/download','MakeChartletController@download');

                });

                // 视频制作，效果信息的接口，此处需要用户登录
                $api->group(['prefix' => '/effects'],function($api){

                    // 下载
                    $api->post('/download','MakeEffectsController@download');

                });

                // 视频制作，滤镜信息的接口，此处需要用户登录
                $api->group(['prefix' => '/filter'],function($api){

                    // 下载
                    $api->post('/download','MakeFilterController@download');

                });

                // 用户点赞的动态
                $api->group(['prefix' => '/like'],function($api){

                    // 下载
                    $api->post('/tweets','TweetController@postLikeTweets');

                });

                /**
                 * 支付
                 */
                // 可用支付方式
                $api -> post('pay/type', 'PayController@payType');

                // 调用支付
                $api -> post('pay/order', 'PayController@pay');

                // 订单状态
                $api -> post('pay/status', 'PayController@status');

                // 取消订单
                $api -> post('pay/cancel', 'PayController@cancel');

                /**
                 * 动态Tweets api
                 */
                $api->group(['prefix' => 'tweets'],function($api){

                    //动态
                    $api->post('/','TweetController@create')
                        ->where('id','[0-9]+');

                    $api->post('/{tweet_id}','TweetController@destroy')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');
                    //动态END

                    //动态-点赞
                    $api->post('/{tweet_id}/likes','TweetLikeController@create')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');

                    //TODO 获取某条动态的点赞情况

                    // 取消动态点赞
                    $api->post('/{tweet_id}/unlikes','TweetLikeController@destroy')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');
                    //动态-点赞 END

                    //动态-回复/评论
                    $api->post('/{tweet_id}/replies','TweetReplyController@create')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');

                    // 删除评论或回复
                    $api->post('/{tweet_id}/replies-delete/{reply_id}','TweetReplyController@destroy')
                        ->where('id','[0-9]+')
                        ->where('reply_id','[0-9]+')
                        ->where('tweet_id','[0-9]+');
                    //动态-回复 END

                    // TODO 动态置顶
                    //动态-置顶
                    $api->post('/{tweet_id}/top_add','TweetController@topAdd')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');

                    //动态-取消置顶
                    $api->post('/{tweet_id}/top_delete','TweetController@topDelete')
                        ->where('id','[0-9]+')
                        ->where('tweet_id','[0-9]+');

                });

                /**
                 * 评论点赞
                 */
                $api->group(['prefix' => 'replies'],function($api){

                    //评论-点赞
                    $api->post('/{reply_id}/likes','TweetReplyLikeController@create')
                        ->where('id','[0-9]+')
                        ->where('reply_id','[0-9]+');

                    // 取消-评论点赞
                    $api->post('/{reply_id}/unlikes','TweetReplyLikeController@destroy')
                        ->where('id','[0-9]+')
                        ->where('reply_id','[0-9]+');
                    //评论-点赞 END
                });

                /**
                 * 用户账户
                 */
                $api->group(['prefix' => 'account'],function($api){

                    // 账户金额查询
                    $api->post('/amount','UserAccountController@index')
                        ->where('id','[0-9]+');
                });

                /**
                 * 收藏相关接口
                 */
                $api->group(['prefix' => 'collection'],function($api){

                    // 收藏详情
                    $api -> post('/','CollectionsController@index');

                    // 添加收藏
                    $api -> post('/{type_id}/add','CollectionsController@create')
                         ->where('type_id','[0-9]+');

                    // 取消收藏
                    $api -> post('/{type_id}/delete','CollectionsController@destroy')
                         ->where('type_id','[0-9]+');
                    //收藏 END
                });

                /**
                 * 私信
                 */
                $api->group(['prefix'=>'letter'],function($api){

                    // 发送私信接口
                    $api->post('/send','PrivateLetterController@create')
                        ->where('id','[0-9]+')
                        ->where('to_id','[0-9]+');

                    // 查询私信前20条数据接口
                    $api->get('/receive','PrivateLetterController@index')
                        ->where('id','[0-9]+');

                    // 查询私信数量接口
                    $api->get('/count','PrivateLetterController@count')
                        ->where('id','[0-9]+');

                    // 删除私信接口
                    $api->post('/delete','PrivateLetterController@delete')
                        ->where('id','[0-9]+');
                });

                // 获取用户关注的用户动态（首页关注页面）
                $api->post('/attention/tweets','TweetController@attentionIndex')
                    ->where('id','[0-9]+');

                /**
                 * 订阅
                 */
                $api->group(['prefix' => 'subscriptions'],function($api){

                    $api->get('/','SubscriptionController@index')
                        ->where('id','[0-9]+');

                    // 获取订阅动态
                    $api->get('/tweets','TweetController@subscriptionIndex')
                        ->where('id','[0-9]+');

                    // 订阅动态是否有更新
                    $api->get('/count','TweetController@focusTweet')
                        ->where('id','[0-9]+');

                    // 添加关注某一用户
                    $api->post('add/{sub_id}','SubscriptionController@create')
                        ->where('sub_id','[0-9]+');

                    // 取消关注某一用户
                    $api->post('delete/{sub_id}','SubscriptionController@delete')
                        ->where('id','[0-9]+')
                        ->where('sub_id','[0-9]+');

                    // 首页关注 顶部用户头像
                    $api->post('/attention','SubscriptionController@attention')
                        ->where('id','[0-9]+');
                });

                /**
                 * 好友
                 */
                $api->group(['prefix' => 'friends'],function($api){
                    $api->get('/','FriendController@index');

                    $api->get('/{friend_id}','FriendController@show')
                        ->where('id','[0-9]+')
                        ->where('friend_id','[0-9]+');

                    $api->post('/{friend_id}','FriendController@store')
                        ->where('id','[0-9]+')
                        ->where('friend_id','[0-9]+');

                    $api->put('/{friend_id}','FriendController@update')
                        ->where('id','[0-9]+')
                        ->where('friend_id','[0-9]+');

                    $api->delete('/{friend_id}','FriendController@destroy')
                        ->where('id','[0-9]+')
                        ->where('friend_id','[0-9]+');
                });

                /**
                 * 用户名获取ID
                 */
                $api->group(['prefix' => '/usernames'],function($api){
                    $api->get('/{username}','UserController@searchUsername')
                        ->where('id','[0-9]+');
                });

                /**
                 * 黑名单
                 */
                $api->group(['prefix' => '/blacklists'],function($api){
                    $api->post('/','BlacklistController@index');

                    $api->post('/add/{blocked_id}','BlacklistController@store');

                    $api->post('/delete/{blocked_id}','BlacklistController@destroy');
                });

                /**
                 * 提醒
                 */
                $api->group(['prefix' => '/notifications'],function($api){
                    $api->get('/','NotificationController@index')
                        ->where('id','[0-9]+');

                    // 新增，获取具体提醒信息
                    $api->post('/notice','NotificationController@message')
                        ->where('id','[0-9]+');

                    $api->delete('/{notice_id}','NotificationController@destroy')
                        ->where('id','[0-9]+')
                        ->where('notice_id','[0-9]+');
                });

                // 用户需求
                $api->group(['prefix' => '/demand'],function($api){

                    $api->post('/add','UserDemandController@add')
                        ->where('id','[0-9]+');

                    $api->post('/cities','UserDemandController@cities')
                        ->where('id','[0-9]+');

                    $api->post('/insert','UserDemandController@insert')
                        ->where('id','[0-9]+');
                });

                // 用户租赁
                $api->group(['prefix' => '/lease'],function($api){

                    $api->post('/types','UserLeaseController@types')
                        ->where('id','[0-9]+');

                    $api->post('/delete','UserLeaseController@delete')
                        ->where('id','[0-9]+');

                    $api->post('/insert','UserLeaseController@insert')
                        ->where('id','[0-9]+');
                });

                // 用户项目,需要登录验证部分
                $api->group(['prefix' => '/project'],function($api){

                    $api->post('/add','UserProjectController@add')
                        ->where('id','[0-9]+');

                    $api->post('/insert','UserProjectController@insert')
                        ->where('id','[0-9]+');

                });

                // 用户云相册
                $api->group(['prefix' => '/cloud','middleware' => ['app.cloud']],function($api){

                    // 查看文件
                    $api->post('/files','UserCloudController@files')
                        ->where('id','[0-9]+');

                    // 查看文件夹
                    $api->post('/folders','UserCloudController@folders')
                        ->where('id','[0-9]+');

                    // 查看用户剩余空间
                    $api->post('/space','UserCloudController@space')
                        ->where('id','[0-9]+');

                    // 添加文件
                    $api->post('/add-file','UserCloudController@addFile')
                        ->where('id','[0-9]+');

                    // 保存文件
                    $api->post('/insert-file','UserCloudController@insertFile')
                        ->where('id','[0-9]+');

                    // 添加文件夹
                    $api->post('/insert-folder','UserCloudController@insertFolder')
                        ->where('id','[0-9]+');

                    // 删除文件
                    $api->post('/delete-file','UserCloudController@deleteFile')
                        ->where('id','[0-9]+');

                    // 删除文件夹
                    $api->post('/delete-folder','UserCloudController@deleteFolder')
                        ->where('id','[0-9]+');

                    // 重命名文件
                    $api->post('/rename-file','UserCloudController@renameFile')
                        ->where('id','[0-9]+');

                    // 重命名文件夹
                    $api->post('/rename-folder','UserCloudController@renameFolder')
                        ->where('id','[0-9]+');

                    // 移动文件
                    $api->post('/remove-file','UserCloudController@removeFile')
                        ->where('id','[0-9]+');

                    // 用户云空间所有的图片视频
                    $api->post('/effect','UserCloudController@effect')
                        ->where('id','[0-9]+');

                });

                // 用户角色发布
                $api->group(['prefix' => '/role'],function($api){

                    $api->post('/add','UserRoleController@add')
                        ->where('id','[0-9]+');

                    $api->post('/insert','UserRoleController@insert')
                        ->where('id','[0-9]+');
                });

                // 赛事
                $api->group(['prefix' => '/competition'],function($api){

                    // 发布赛事
                    $api->post('/insert','CompetitionController@insert')
                        ->where('id','[0-9]+');

                    // 判断用户是否已经参与过该赛事
                    $api->post('/{competition_id}/check','CompetitionController@check')
                        ->where('id','[0-9]+')
                        ->where('competition_id','[0-9]+');

                    // 用户发布的赛事
                    $api->post('/release','CompetitionController@release')
                        ->where('id','[0-9]+');

                    // 用户参与的赛事
                    $api->post('/participation','CompetitionController@participation')
                        ->where('id','[0-9]+');
                });

                /**
                 * 群聊
                 */
                $api->group(['prefix' => '/chat-groups'],function($api){
                    $api->post('/','ChatGroupController@create');

                    $api->delete('/{group_id}','ChatGroupController@destroy');

                    $api->put('/{group_id}','ChatGroupController@update');

                    $api->group(['prefix' => '{group_id}/members'],function($api){
                        $api->post('/','ChatGroupMemberController@store');

                        $api->delete('/','ChatGroupMemberController@destroy');

                        $api->delete('{member_id}','ChatGroupMemberController@destroySelf');


                    });
                });

                /**
                 * 标签
                 */
                $api->group(['prefix' => '/tags'],function($api){
                    $api->get('/','TagController@index')
                        ->where('id','[0-9]+');

                    $api->post('/','TagController@store')
                        ->where('id','[0-9]+');

                    $api->delete('/{tag_id}','TagController@destroy')
                        ->where('id','[0-9]+')
                        ->where('tag_id','[0-9]+');

                    $api->put('/{tag_id}','TagController@update')
                        ->where('id','[0-9]+')
                        ->where('tag_id','[0-9]+');
                });

                $api->group(['prefix' => 'channels'],function($api){
                    $api->get('/','ChannelController@userIndex');

                    $api->post('/','ChannelController@userStore');

                });
            });
        });

        // 用户项目,不需要登录验证部分
        $api->group(['prefix' => '/project/{id}'],function($api){

            // 项目投资详情
            $api->post('/details','UserProjectController@details')
                ->where('id','[0-9]+');

            // 项目投资页面
            $api->post('/invest','UserProjectController@invest')
                ->where('id','[0-9]+');

            // 项目支持页面
            $api->post('/support','UserProjectController@support')
                ->where('id','[0-9]+');
        });

        /**
         * 用户帮助与反馈
         */
        $api->group(['prefix'=>'help'],function($api){

            // 帮助标题
            $api->post('/index','HelpFeedbackController@helpName');

            // 反馈
            $api->post('/feedback','HelpFeedbackController@feedback');
        });

        /**
         * 举报接口
         */
        $api->group(['prefix' => 'complains'],function ($api) {

            // 举报接口 举报原因
            $api->post('/causes', 'ComplainController@index');

            $api->group(['middleware' => 'jwt.auth'], function ($api) {

                // 举报接口 创建
                $api->post('/add', 'ComplainController@create');
            });
        });

        // 粉丝 关注
        $api->group(['middleware' => 'jwt.auth'], function ($api) {

            $api->group(['prefix' => 'users'],function ($api) {

                // 粉丝
                $api->post('{id}/subscriptions/follower', 'SubscriptionController@follower');

                // 关注
                $api->post('{id}/subscriptions/following', 'SubscriptionController@following');
            });
        });

        // 用户可为登录状态，或者为非登录状态
        $api->group(['middleware' => ['app.user']], function ($api) {

            // 创作首页
            $api -> group(['prefix' => '/creation'],function($api){

                // 创作首页的封面
                $api->post('/cover','MakeTemplateController@cover');

                // 创作首页的广告按钮
                $api->post('/ads','MakeTemplateController@ads');
            });

            // 视频制作，此为不需要用户登录的接口，需要用户登录的接口在另一处
            $api->group(['prefix' => '/make'],function($api){

                // 音频信息
                $api->group(['prefix' => '/audio'],function($api){

                    // 音乐文件首页
                    $api->post('/file','MakeAudioController@file');

                    // 音乐目录
                    $api->post('/folder','MakeAudioController@folder');

                    // 音效文件首页
                    $api->post('/effect/file','MakeAudioEffectController@file');

                    // 音效目录
                    $api->post('/effect/folder','MakeAudioEffectController@folder');

                });

                // 视频制作的模板
                $api->group(['prefix' => '/template'],function($api){

                    // 首页
                    $api->post('/folder','MakeTemplateController@folder');
                    $api->post('/index','MakeTemplateController@index');
                    $api->post('/recommend','MakeTemplateController@recommend');
                    $api->post('/details','MakeTemplateController@details');

                    // 下载
                    $api->post('/download', 'MakeTemplateController@download');

                });

                // 贴图信息
                $api->group(['prefix' => '/chartlet'],function($api){

                    // 贴图文件首页
                    $api->post('/file','MakeChartletController@index');

                    // 贴图目录
                    $api->post('/folder','MakeChartletController@folder');
                });

                // 效果信息
                $api->group(['prefix' => '/effects'],function($api){

                    // 效果文件首页
                    $api->post('/file','MakeEffectsController@index');

                    // 效果目录
                    $api->post('/folder','MakeEffectsController@folder');
                });

                // 滤镜信息
                $api->group(['prefix' => '/filter'],function($api){

                    // 滤镜文件首页
                    $api->post('/file','MakeFilterController@index');

                    // 滤镜目录
                    $api->post('/folder','MakeFilterController@folder');
                });

                // 视频制作，字体 font
                $api->group(['prefix' => '/font'],function($api){

                    // 字体列表
                    $api->post('/file','MakeFontController@file');

                });

            });

            // 首页关注页面，用户未登录
            $api->group(['prefix' => 'attention'], function ($api) {

                // 推荐好友
                $api->post('recommend','UserController@recommend');

                // 明星达人
                $api->post('star','UserController@star');

                // 通讯录好友
                $api->post('phone','AttentionController@phone');

                // 微博好友
                $api->post('weibo','AttentionController@weibo');

                // 附近的人
                $api->post('nearby','UserController@nearby');
            });

            // 将此搬家至此，放在中间件下,方便获取登录用户信息
            $api->group(['prefix' => 'users'], function ($api) {

                // 搜索用户昵称
                $api->get('search','UserController@search');

                // 获取某个用户的信息
                $api->get('/{id}','UserController@show')
                    ->where('id','[0-9]+');


                // 获取某个用户的动态
                $api->get('/{id}/tweets','TweetController@index')
                    ->where('id','[0-9]+');

                // 搜索某个用户的动态
                $api->post('/{id}/tweets-search','TweetController@tweetSearch')
                    ->where('id','[0-9]+');

                // 发现页面热门用户
                $api->post('ranking','UserController@ranking');
            });

            // 赛事
            $api -> group(['prefix' => 'activity'],function ($api) {

                // 轮播广告
                $api -> post('advertising', 'ActivityController@rotation');

                // 赛事列表
                $api -> post('list', 'ActivityController@index');

                // 赛事--动态详情
                $api -> post('tweet', 'TweetController@activityTweetsDetails');

                // 赛事--动态评论
                $api -> post('reply', 'ActivityController@tweetReply');
            });

            // 频道相关
            $api->group(['prefix' => 'channels'],function ($api) {

                $api->get('/','ChannelController@index');

                // 获取频道的动态详情信息  例：http://www.goobird.com/api/channels/2/tweets
//                $api->get('{id}/tweets','TweetController@channelTweets')
//                    ->where('id','[0-9]+');

                // 获取频道的动态详情信息  例：http://www.goobird.com/api/channels/2/tweets
                $api->get('{id}/tweets','TweetController@channelNewTweets')
                    ->where('id','[0-9]+');

                // 频道热度榜，用户头像
                $api->get('{id}/ranking','ChannelController@ranking')
                    ->where('id','[0-9]+');

                // 频道详情
                $api->post('{id}/details','ChannelController@details')
                    ->where('id','[0-9]+');
            });

            // 动态 tweets  将此搬家至此，放在中间件下,方便获取登录用户信息
            $api->group(['prefix' => 'tweets'],function($api) {

                //
                $api->get('{id}/likes','TweetLikeController@index')
                    ->where('id','[0-9]+');

                // 获取同一位置的动态
                $api -> post('/location', 'TweetController@locationTweets');

                // 获取某个动态的详情
                $api->post('{id}/details','TweetController@details')
                    ->where('id','[0-9]+');

                // 获取某个动态的详情
                $api->post('{id}/information','TweetController@information')
                    ->where('id','[0-9]+');

                // 获取某个动态下评论的详情
                $api->post('{id}/reply/{reply_id}/details','TweetReplyController@details')
                    ->where('id','[0-9]+')
                    ->where('reply_id','[0-9]+');

                // 搜索动态 20161206
                $api -> get('/search','TweetController@search');
            });

            /**
             * 奖杯接口
             */
            $api -> group(['prefix' => 'trophy'],function($api){

                // 未登录状态，获取奖杯配置情况
                $api -> post('/information','TweetTrophyController@information');

                // 获取单个作品的奖杯情况
                $api -> post('/details','TweetTrophyController@details');

                // 登录状态
                $api->group(['middleware' => 'jwt.auth'], function ($api) {

                    // 赠送奖杯
                    $api -> post('/present','TweetTrophyController@present');

                    // 购买奖杯
                    $api -> post('/buy','TweetTrophyController@buy');

                    // 个人中心的奖杯详情
                    $api -> post('/profiles','TweetTrophyController@profiles');
                });

            });

            /**
             * 发现页面
             */
            $api->group(['prefix' => 'discovery'],function($api){

                // 发现页面首页
                $api -> post('/','DiscoveryController@index');

                // 附近的动态
                $api -> post('/nearby','DiscoveryController@nearby');

                // 大家都在看
                $api -> post('/watching','DiscoveryController@watching');

                // 大家都在搜
                $api -> post('/search','DiscoveryController@search');

                // 精选媒体
                $api -> post('/featured','DiscoveryController@featured');

            });

            // 话题
            $api->group(['prefix' => 'topics'],function($api) {

                // 获取某一话题下的动态 topics/tweets
                $api->get('/tweets','TweetController@topics');

                // 获取某一话题下的热门动态
                $api->post('/{id}/hot','TweetController@topicTweets');

                // 获取热门话题 topics/hot
                $api->post('/hot','TopicController@hotTopic');

                // 获取推荐话题 topics/recommend
                $api->post('/recommend','TopicController@recommendTopic');

                // 获取全部话题 topics/all
                $api->post('/all','TopicController@allTopics');

                // 搜索话题 20161206
                $api -> get('/search','TopicController@search');

                // 话题详情
                $api -> post('/{id}/details','TopicController@details')
                     ->where('id','[0-9]+');

                // 话题参与者信息
                $api -> post('/participants','TopicController@participants');
            });

            // 赛事
            $api->group(['prefix' => 'competition'],function($api) {

                // 获取某一赛事下的热门动态
                $api->post('/{id}/hot','TweetController@competitionTweets');

                // 获取热门赛事 topics/hot
//                $api->post('/hot','CompetitionController@hotTopic');

                // 获取全部赛事 topics/all
//                $api->post('/all','CompetitionController@allTopics');

                // 搜索赛事
//                $api -> post('/search','CompetitionController@search');

                // 赛事详情
                $api -> post('/{id}/details','CompetitionController@details')
                    ->where('id','[0-9]+');
            });
        });

        /**
         * 滤镜Blurs api
         */
        $api->group(['prefix' => 'blurs'],function($api){
            $api->get('/{id}','BlurController@show')
                ->where('id','[0-9]+');

            $api->get('/','BlurController@index');
        });

        $api->group(['prefix' => 'blur-classes'],function($api){
            $api->get('/','BlurClassController@index');

            $api->get('/{id}/preview','BlurClassController@preview')
                ->where('id','[0-9]+');

            $api->get('/{id}','BlurClassController@install')
                ->where('id','[0-9]+');
        });

        // 热门动态
        $api->group(['prefix' => 'popular','middleware' => ['app.user']],function ($api) {
            $api->get('/tweets','TweetController@popularIndex');
        });

        $api->group(['prefix' => 'topics','middleware' => 'jwt.auth'],function ($api) {
            $api->get('/','TopicController@index');
        });

        // 各类协议
        $api->group(['prefix' => 'agreement'],function($api){

            // 注册协议
            $api->post('/register','AgreementController@register');

            // 举报规范
            $api->post('/report','AgreementController@report');

            // 发起需求协议
            $api->post('/sponsor','AgreementController@sponsor');

            // 投资协议
            $api->post('/invest','AgreementController@invest');

            // 租赁协议
            $api->post('/lease','AgreementController@lease');

            // 发布角色协议
            $api->post('/role','AgreementController@role');
        });

        // 视频自动播放，次数+1
        $api->post('/video/increment/{id}','TweetPlayController@videoIncrement')
            ->where('id','[0-9]+');
        //赛事搜索
        $api->post('/activity/search','ActivitySearchController@search');



        /**
         *   片段  fragment
         */

        $api->group(['prefix' => 'fragment'],function($api){

            // 片段首页
            $api -> post('/','FragmentController@index');

            // 附近的片段
            $api -> post('/nearby','FragmentController@nearby');

            // 收藏表
            $api -> post('/collect','FragmentController@collect');

            // 大家都在搜
           // $api -> post('/search','DiscoveryController@search');

            // 精选媒体
          //  $api -> post('/featured','DiscoveryController@featured');

        });

        //获取某个人的用户信息  通过姓名
        $api->post('/person','UserController@person' );

        //后增话题详情
        $api->post('/topics/details','TopicController@afterdetails');

        //赛事搜索
        $api->post('/activity/search','ActivitySearchController@search');



        /**
         *   片段  fragment
         */

        $api->group(['prefix' => 'fragment'],function($api){

            // 片段首页
            $api -> post('/','FragmentController@index');

            // 附近的片段
            $api -> post('/nearby','FragmentController@nearby');

            // 分类详情
            $api -> get('/details/{id}','FragmentController@details')
                 -> where('id','[0-9]+');

            //最新片段
            $api -> get('/newlist/{id}','FragmentController@newlist')
                -> where('id','[0-9]+');

            //片段预览
            $api -> get('fragdetail/{id}','FragmentController@fragdetail')
                 -> where('id','[0-9]+');

            //片段详情
            $api->get('fragmentdetails/{id}','FragmentController@fragmentdetails')
                ->where('id','[0-9]+');

            //片段分类内热门与最新
            $api->get('fraglists/{id}','FragmentController@fraglists')
                ->where('id','[0-9]+');

//            $api->group(['middleware' => 'jwt.auth'],function ($api) {

                // 收藏
                $api -> post('/collect','FragmentController@collect');

                // 下载
                $api -> post('/download','FragmentController@download');

                //使用且开拍
                $api -> post('useOrFilm/{fram_id}','FragmentController@useOrFilm')
                    ->where('fram_id','[0-9]+');
//            });


        });

        //获取某个人的用户信息  通过姓名
        $api->post('/person','UserController@person' );

        //后增话题详情
        $api->post('/topics/details','TopicController@afterdetails');

        //置顶
        $api->get('/topper','TopperController@index');
    });
});