<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $tweet['content'] }}</title>

    <meta name="description" content="{{ $tweet['content'] }}">
    <meta name="keywords" content="追喜">
    <meta name="application-name" content="{{ $tweet['content'] }}">
    <meta name="msapplication-tooltip" content="{{ $tweet['content'] }}">

    {{--<meta property="qc:admins" content="网站QQ登录码">--}}

    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <meta content="telephone=no" name="format-detection">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta name="author" content="zhuixi.com">
    <meta name="copyright" content="Copyright ©goobird.com 版权所有">
    <meta name="revisit-after" content="1 days">
    <meta name="MobileOptimized" content="320">
    <meta name="apple-itunes-app" content="app-id=apple-itunes-app-ID编号">
    <meta name="HandheldFriendly" content="True">
    <link rel="stylesheet" href="/m-movie/a/mobile-base.css">
    <link rel="apple-touch-icon" href="http://www.zhuixi.com/touch-icon-iphone.png">
    <link rel="apple-touch-icon" sizes="76x76" href="http://www.zhuixi.com/touch-icon-ipad.png">
    <link rel="apple-touch-icon" sizes="120x120" href="http://www.zhuixi.com/touch-icon-iphone-retina.png">
    <link rel="apple-touch-icon" sizes="152x152" href="http://www.zhuixi.com/touch-icon-ipad-retina.png">
    {{--<script src="/m-movie/a/hm.js.下载"></script>--}}
    <script type="text/javascript" src="http://libs.baidu.com/jquery/1.10.2/jquery.min.js"></script>

    <script type="text/javascript">
        $(function () {
            $.extend({
                alert: function (obj) {
                    var def = {fade: 200, delay: 1500, tip: "操作成功"};
                    var o = $.extend({}, def, obj);
                    var imgUrl = (o.error == undefined) ? "xm_tip_ok.png" : "xm_tip_error.png";
                    $('#xm-tip-dialog').remove();
                    var dom = '<div class="xm-tip-dialog" id="xm-tip-dialog">' +
                            '<img src="' + Think.IMG + '/' + imgUrl + '"/><span>' + o.tip + '</span>' +
                            '</div>';
                    $(dom).appendTo('body');
                    $('#xm-tip-dialog').css({
                        marginLeft: -$('#xm-tip-dialog').width() / 2
                    }).fadeIn(o.fade).delay(o.delay).fadeOut(o.fade, function () {
                        $(this).remove();
                        if (o.complete != undefined && typeof o.complete == "function")
                            o.complete();
                    });
                }
            });
            function hengshuping() {
                if (window.orientation == 180 || window.orientation == 0) {
                    // alert("竖屏状态！")
                    var _width = $(window).width();
                    $('.video_iframe').attr({height: _width * 5 / 8});
                }
                if (window.orientation == 90 || window.orientation == -90) {
                    // alert("横屏状态！")
                    var _width = $(window).width();
                    $('.video_iframe').attr({height: _width * 5 / 8});
                }
            }

            // window.onorientationchange = window.onresize = hengshuping();
            $(window).on('orientationchange', function () {
                hengshuping();
            });
            $(window).on('resize', function () {
                hengshuping();
            })
        });
        var scale = 1.0, ratio = 1;
        (function () {
            if (window.devicePixelRatio === 2 && window.navigator.appVersion.match(/iphone/gi)) {
                scale = 0.9;
                ratio = 2;
            }
            if (window.devicePixelRatio === 1.5) {
                scale = 0.9;
            }
            var text = '<meta name="viewport" content="initial-scale=' + scale + ', maximum-scale=' + scale + ', minimum-scale=' + scale + ',width=device-width",minimal-ui />';
            document.write(text);
        })();
        window.onload = function () {
            var menuBtn = $('#wrap-pop'),
                    userBtn = $('#user-pop'),
                    menuBg = $('#menu-bg-fix'),
                    userBg = $('#user-bg-fix'),
                    pindao = $('#pindao');

            menuBtn.click(function () {
                if (userBtn.hasClass('user-pop-sel')) {
                    userBg.hide();
                    userBtn.removeClass('user-pop-sel');
                }
                if (menuBtn.hasClass('wrap-pop-sel')) {
                    menuBg.hide();
                    $('#header').removeClass('header-fix');
                    menuBtn.removeClass('wrap-pop-sel');
                } else {
                    $('#header').addClass('header-fix');
                    menuBg.show();
                    menuBtn.addClass('wrap-pop-sel');
                }
            });
            userBtn.click(function () {
                if (menuBtn.hasClass('wrap-pop-sel')) {
                    menuBg.hide();
                    menuBtn.removeClass('wrap-pop-sel');
                }
                if (userBtn.hasClass('user-pop-sel')) {
                    userBg.hide();
                    $('#header').removeClass('header-fix');
                    userBtn.removeClass('user-pop-sel');
                } else {
                    $('#header').addClass('header-fix');
                    userBg.show();
                    userBtn.addClass('user-pop-sel');
                }
            });
            pindao.click(function () {
                if ($('#pindao .tri').hasClass('up')) {
                    $('#pindao .tri').removeClass('up');
                    $('.nav-list .sub').slideUp();
                } else {
                    $('#pindao .tri').addClass('up');
                    $('.nav-list .sub').slideDown();
                }
            });


            $(document).bind('click', function (e) {
                var target = $(e.target);
                if (target.closest("#wrap-pop").length == 1 || target.closest("#user-pop").length == 1 || target.closest('#header-pop').length == 1)
                    return;
                if (target.closest("#menu-bg-fix").length == 0 || target.closest("#user-bg-fix").length == 0) {
                    userBg.hide();
                    menuBg.hide();
                    $('#header').removeClass('header-fix');
                    userBtn.removeClass('user-pop-sel');
                }
            });

        }
        var postid = 0;
    </script>
    <meta name="viewport" content="initial-scale=0.9, maximum-scale=0.9, minimum-scale=0.9,width=device-width" ,minimal-ui="">
    <script type="text/javascript" name="baidu-tc-cerfication" src="http://apps.bdimg.com/cloudaapi/lightapp.js"></script>
    <script type="text/javascript">window.bd && bd._qdc && bd._qdc.init({app_id: ''});</script>
    <link rel="stylesheet" type="text/css" href="https://player.youku.com/h5player/play.css?ver=2016/12/28154220">
    <script src="http://bdimg.share.baidu.com/static/js/logger.js?cdnversion=412250"></script>
    <link href="http://bdimg.share.baidu.com/static/css/bdsstyle.css?cdnversion=20131219" rel="stylesheet" type="text/css">
    <link href="/m-movie/a/details.css" rel="stylesheet" type="text/css">
</head>
<body class="art-body" style="">
<iframe frameborder="0" style="display: none;" src="/m-movie/a/saved_resource.html"></iframe>


<!-- 主体 -->

<div id="main-container" class="main">

    <script>
        var __console = console;
        var __log = console.log;
        function log() {
            __log.apply(__console, arguments);
        }

    </script>

    <div class="weixin-wrap">
        <div class="weixin-cover">
            <span class="text">正在前往「ZhuiXi」<br>
                <span class="small-text">请点击右上角“···”,在Safair中打开</span>
                <img src="/m-movie/a/to-share.png">
            </span>
        </div>
    </div>
    <div class="go-down">
        <div class="go-down-content">
            <div class="img-wrap">
                <img class="go-down-logo" src="/m-movie/a/go-down-img.jpg" alt="">
                <p class="download-v">请先下载「ZhuiXi」客户端<br>
                    <span>get更多精彩内容和体验</span>
                </p>
            </div>
            <a class="log" href="http://www.goobird.com/app/download">
                <div class="download-zhuixi">立即下载</div>
            </a>
            <span class="go-down-cancel"></span>
        </div>
    </div>
    <div class="loading" style="">
        <div class="loader">
            <div class="loader-inner ball-beat">
                <svg xmlns="http://www.w3.org/2000/svg" width="48px" height="48px" viewBox="0 0 48 48">
                    <circle cx="6" cy="24" r="6px" class="circle" fill="#000"></circle>
                    <circle cx="24" cy="24" r="6px" class="circle" fill="#000"></circle>
                    <circle cx="42" cy="24" r="6px" class="circle" fill="#000"></circle>
                </svg>

            </div>
        </div>

        <!-- <div class="cssload-loader"></div> -->
    </div>
    <div>
        <div class="share-cover">
            <div class="share-arrow"><em></em></div>
            <span class="share-text">点击右上角<br>分享该视频</span>
        </div>
    </div>

    <div class="first-video" data-video="http://v.youku.com/v_show/id_XMTg5NzQzMTM0OA==.html"
         data-qiniu="http://qiniu.zhuixi.zhuixicdn.com/586c597faeca9.mp4" style="height: 233px;">
        <div class="video-face log" data-log="顶部视频点击播放数">
            <img src="{{ $tweet['picture'].'?imageMogr2/thumbnail/960x960/gravity/Center/crop/960x540/format/jpg' }}" width="100%">
            <span class="play-btn" style="display: inline;"></span>
            <div class="bot-bar"></div>
            <span class="play-duration">{{ $tweet['duration'] }}</span>
        </div>
        <video class="video" controls="controls" src="{{ $tweet['video'] }}"
               width="100%" webkit-playsinline=""></video>
    </div>

    <div class="comment comment-new-warp comment-visiable" id="comment">
        <div class="com-list" id="com-list" style="position:relative;">
            <li class="clearfix">
                <div class="com-img ww zg" data-zg="作品用户头像">
                    <img class="full" src="{{ $tweet['user']['avatar'] ? $tweet['user']['avatar'] : '/m-movie/a/default.png' }}" alt="头像">
                    @if($tweet['user']['verify'])
                        <img src="{{ $tweet['user']['verify'] == 1 ? '/m-movie/a/user_v_r.png' : '/m-movie/a/user_v_b.png' }}" class="f-vip" width="16" height="16" style="position:absolute;z-index:2;top:36px;left:21px;">
                    @endif
                </div>
                <div class="com-text-wx">
                    <div style="position:relative;">
                        <div class="title">
                            <span><b>{{ $tweet['user']['nickname'] }}</b></span>
                        </div>
                        <div style="position: absolute;right:0px;top:0px" class="title"><span><b>{{ $tweet['tweet_grade'] ?: '暂无评' }}分</b></span></div>
                    </div>
                    <div class="ope" style="margin-top: 3px;position:relative;">
                        <div>
                            <span class="time">{{ $tweet['created_at'] }}</span>
                        </div>
                        <div class="new-rating hot-rating" id="tweet_star" style="position: absolute;right:0px;top:-5px" data-rating="{{ $tweet['tweet_grade'] }}">
                            <span class="">
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        </div>
    </div>

    {{--<script type="text/javascript" src="/m-movie/a/jsapi"></script>--}}
    <div class="new-art-content wx-art-content fs show-part">
        <!-- 内容 -->
        <p>{{ $tweet['content'] }}</p>
    </div>

    <h2 class="item-title fs" style="margin-bottom:10px;margin-top:20px;">相关推荐</h2>

    @if(!empty($relate))
        <ul class="hot-list m0 fs">
            @foreach($relate as $item)
                <li class="hot-list-item padding2 active-change-bg1 m0 log zg">
                    <a class="pic new-view-link" href="{{ asset('/tweet/'.$item['id']) }}">
                        <div class="tuijian-img-box" style="min-height:75px;">
                            <img class="item-img" src="{{ $item['picture'].'?imageMogr2/thumbnail/360x360/gravity/Center/crop/360x240/format/jpg' }}">
                            <span class="dur" style="background:none;font-size:12px;right:2px;">{{ $item['duration'] }}</span>
                        </div>
                    <span class="descript inb fs3 fr " style="margin:0;padding-top:5px;">
                        <span class="line-hide-2 video-title">{{ $item['content'] }}</span>
                        <div class="new-rating hot-rating" data-rating="{{ $item['tweet_grade'] }}">
                            <span class="">
                                @if($item['tweet_grade'])
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <em class="half-bg"></em>
                                    <span class="num">{{ $item['tweet_grade'] }}分</span>
                                @else
                                    <span class="num">暂无评分</span>
                                @endif
                            </span>
                            <span class="" style="margin-left: 10px;">
                                <em class="browe-bg"></em>
                                <span style="font-size: 13px">{{ $item['browse_times'] }}次</span>
                            </span>
                        </div>
                    </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
    <!--  <div class="line-bar"></div> -->
    <h2 class="item-title fs comment-title comment-visiable" style="margin:60px 15px 20px;">评论</h2>
    <div class="comment comment-new-warp comment-visiable" id="comment">
        <ul class="com-list" id="com-list">
            @if(!empty($replys))
                @foreach($replys as $item)
                    <li class="clearfix" >
                    <div class="com-img ww zg" data-zg="点击评论头像">
                        <img class="full" src="{{ $item['user']['avatar'] ? $item['user']['avatar'] : '/m-movie/a/default.png' }}" alt="头像">
                    </div>
                    <div class="com-text-wx">
                        <div class="title zg" data-zg="点击评论用户名"><span>{{ $item['user']['nickname'] }}</span></div>
                        <div class="intro zg" data-zg="点击评论内容">{{ $item['content'] }}</div>
                        <div class="ope"><span class="time">{{ $item['created_at'] }}</span></div>
                    </div>
                </li>
                @endforeach
            @endif
        </ul>
        <div class="comment-pager" id="comment-pager"><span class="cur" data-p="1">1</span></div>
        <a href="javascript:;">
            <div class="see-all-comment log zg" data-log="“打开ZhuiXi查看更多讨论”点击数Android" data-logios="“打开ZhuiXi查看更多讨论”点击数Ios"
                 data-url="zhuixi://video/50840" data-zg="点击打开ZhuiXi查看更多讨论">
                <span>查看更多讨论</span>
            </div>
        </a>
    </div>

    <div class="wx-warp" style="height:5px;">
        {{--<img src="/m-movie/a/56eba3eed3b95.png" alt="">--}}
    </div>


    <div class="fixed-banner" data-log="底部fixed bar点击数">
        <div class="app-down-new-wrap-share" data-log="文章中间的引导下载button">
            <a href="http://www.goobird.com/app/download" class="app-down" id="app-down-btn">
                <span class="app-logo"></span>
                <span class="app-tip">
                    <span class="fs6" style="padding-bottom:2px;">ZhuiXi</span><br>
                    <span>感悟人生只需一刻钟</span>
                </span>
            <span class="app-down-new-btn">
              立即安装
            </span>
            </a>
        </div>
        <a href="http://www.goobird.com/app/download"><img src="http://7xtg0b.com1.z0.glb.clouddn.com/topic/77/14841288205473114_640*112_.gif?imageMogr2/thumbnail/1242x216/gravity/Center/crop/1242x216" alt=""></a>
    </div>
    <script>
        var postid = 50840;
        var commentType = 0;
        var pid = 1;
    </script>
    <script type="text/javascript" id="bdshare_js" data="type=tools" src="http://bdimg.share.baidu.com/static/js/bds_s_v2.js?cdnversion=412251"></script>

    {{--<script type="text/javascript" src="/m-movie/a/jquery.lazyload.min.js.下载"></script>--}}
    <script>
        $(function () {
            var ua = navigator.userAgent.toLowerCase();
            var isWeixin = ua.match(/MicroMessenger/i) == "micromessenger";
            var isAndroid = /android/i.test(ua);
            var isQq = /mqqbrowser/i.test(ua);
            var isIOS = /iphone|ipad|ipod/i.test(ua);
            var widBody = $(window).width();
            var isalbum = 0;
            render();
            function render() {
                $(".tab span").on("click", function () {
                    $(this).addClass("tab-select").siblings().removeClass("tab-select");
                })
                var bodyWidth = $(window).width();

                $(".first-video").height(bodyWidth * 0.5625 - 1);

                if (isAndroid && (isWeixin || isQq)) {
                    if (isWeixin) {
                        $(".share-cover span").text("点击右上角「…」分享该视频");
                    }
                    $(".share-cover div").css("marginTop", bodyWidth * 0.5625 + "px");
                }
                if (isalbum) {
                    $("video").css("margin-bottom", "20px");
                } else {
                    $("video").slice(1).css({"margin-bottom": "20px"});
                }


                $("p").each(function (index) {
                    if (!$(this).html()) {
                        $(this).remove();
                    }
                })

                $(".video .video-face img").width(bodyWidth - 30);
                // $(".video").height((bodyWidth-30)*0.538);
                $(".video,.first-video").on("click", function () {
                    $(this).find(".video-face").hide();
                    $(this).find("video")[0].play();
                })
                // setTimeout(function())
                setTimeout(function () {
                    $(".play-btn").show();
                }, 300)

                // 格式化头视频时长
//                var originDuration = $(".first-duration").text();
//                $(".first-duration").text(originDuration.replace(":", "'") + "''");
//
//                $(".first-video .video-face").append('<span class="play-duration">' + originDuration.replace(":", "'") + "''" + '</span>');
//
//                $(".video").each(function () {
//                    var videoDuration = $(this).find(".video-duration");
//                    var duration = videoDuration.text();
//                    videoDuration.remove();
//                    $(this).find(".video-face").append('<span class="play-duration">' + duration.replace(":", "'") + "''" + '</span>');
//                    $(this).find("video").height((bodyWidth - 30) * 0.5625);
//                })

                if ($(".comment-num").html() == 0) {
                    $(".comment-title,.see-all-comment").hide();
                }

                var ratingNum = parseFloat("7.8");
                // setStar(ratingNum,$(".new-rating"));

                $(".hot-list li .hot-rating").each(function (index) {
                    var hotRating = $(this).data("rating");
                    setStar(hotRating, $(this));
                });

//                $("#tweet_star").on(function (index) {
                    var tweetStar = $("#tweet_star").data("rating");
                    setStar(tweetStar, $("#tweet_star"));
//                });

                $(document).on("touchstart", function () {
                });
                var pauseFlag = true, playFlag = false;
                if ($(".first-video video")[0]) {
                    $(".first-video video")[0].addEventListener("pause", function () {
                        if (pauseFlag) {
                            $(".share-cover,.app-down-new-wrap-share").show();
                            setTimeout(function () {
                                $(".share-cover,.app-down-new-wrap-share").addClass("fadeIn");
                                shareTextAnimate();
                            }, 300)
                            document.body.addEventListener('touchmove', preventDefault, false);
                        }
                    }, false)
                }
                if ($("video")[0]) {
                    $("video")[0].ontouchmove = function () {
                        pauseFlag = false;
                    }
                    $("video")[0].ontouchend = function () {
                        pauseFlag = true;
                    }
                }

                $(".see-all-comment").on("click", function () {
                    // if(!isIOS){
                    clickApp();
                    // }
                })
                $(window).on('orientationchange', function () {
                    var bodyWidth = $(window).width();
                    $(".first-video").height(bodyWidth * 0.5625 - 1);
                });
                if ($(".first-video video")[0]) {
                    $(".first-video video")[0].addEventListener("playing", function () {
                        playFlag = true;
                    }, false)
                }
                // if(isIOS){
                //   $(".see-all-comment").parent().attr("href","http://www.zhuixi.com/app/download");
                //   $(".open-app").hide();
                // }
                $(".open-app").on("click", function () {
                    clickApp();
                });
                function clickApp() {
                    var link = $(".see-all-comment").data("url");
                    if (isWeixin || (isIOS && isQq)) {
                        if ((isWeixin) && isAndroid && playFlag) {
                            $(".weixin-wrap").css("marginTop", widBody * 0.5625 - 1 + "px");
                        }
                        $(".weixin-wrap").show();
                    } else {
                        var openDate = new Date().getTime();
                        setTimeout(function () {
                            var showDate = new Date().getTime();
                            // alert(openDate+"$"+showDate)
                            if (showDate - openDate < 1020) {
                                $(".go-down").show();
                                setTimeout(function () {
                                    $(".go-down").css("opacity", 1);
                                }, 30);
                            }
                        }, 1000)
                        openApp(link);
                    }
                }

                $(".weixin-wrap").on("click", function (e) {
                    $(this).hide();
                })

                function openApp(link) {
                    if (isIOS) {
                        location.href = link;
                    } else {
                        $("body").append($("<iframe>").attr("src", link));
                    }
                }

                $(".go-down").on("click", function (e) {
                    if (e.target == $(".go-down")[0]) {
                        $(".go-down").css("opacity", 0);
                        setTimeout(function () {
                            $(".go-down").hide();
                        }, 300);
                    }
                });
                $(".go-down-cancel").on("click", function () {
                    $(".go-down").css("opacity", 0);
                    setTimeout(function () {
                        $(".go-down").hide();
                    }, 300);
                });
                // }

//                待删除
                {{--if ($(".open-app").length) {--}}
                    {{--var videoImgStr = $(".video-face img").attr("src");--}}
                    {{--var imgSplitArr = videoImgStr.split('@');--}}
                    {{--var openAppCover = imgSplitArr[0] + "@1080w_607h_1e_1c_50-30bl.jpg";--}}
                    {{--$(".open-app-cover").attr("src", openAppCover).css("top", -(bodyWidth * 607 / 1080 - 45) / 2);--}}
                {{--}--}}

//                根据屏幕宽度设置视频封面的宽度
//                var screen_length = $(document.body).width();
//
//                var pic_height = screen_length/414 * 233;
//                var videoImgStr = $(".video-face img").attr("src");


                function preventDefault(event) {
                    event.preventDefault();
                }


                $(".share-cover").on("click", function () {
                    $(".share-cover,.app-down-new-wrap-share").removeClass("fadeIn");
                    setTimeout(function () {
                        $(".share-cover,.app-down-new-wrap-share").hide();
                    }, 300)
                    $("#id").unbind('touchmove');
                    document.body.removeEventListener('touchmove', preventDefault, false);
                })
            }

            imgPre();
            function imgPre() {
                var imgNum = $("img").length, i = 0;
                $("img").each(function () {
                    var img = new Image();
                    img.src = $(this).attr("src");
                    // console.log(i,imgNum);
                    img.onload = function () {
                        i++
                        // console.log(i,imgNum);
                        if (i >= parseInt(imgNum / 2)) {
                            setTimeout(function () {
                                $(".loading").addClass("loading-fade");
                                setTimeout(function () {
                                    $(".loading").hide();
                                }, 1000)
                            }, 800)

                        }
                    }
                })

            }


            function shareTextAnimate() {
                var text = $(".share-cover .share-text").text();
                var textArray = text.split("");
                // var cut = (isAndroid && isWeixin) ? 7 : 4;
                $(".share-cover .share-text").text("");
                textArray.forEach(function (elem, index) {
                    if (index == 4 && !(isAndroid && isWeixin)) {
                        var html = "<i>" + elem + "</i><br>";
                    } else {
                        var html = "<i>" + elem + "</i>";
                    }
                    $(".share-cover .share-text").append(html);
                    var time = Math.random() * 600;
                    setTimeout(function () {
                        $(".share-cover .share-text i").eq(index).css("opacity", "1");
                    }, time)
                })
            }


            //星级设定
            function setStar(ratingNum, target) {
                var target = target.find("em");
                var ratingForNum = Math.ceil(ratingNum / 2);
                // alert(ratingForNum);
                var ratingForLastNum = ratingForNum - 1;
                var ratingNumss = ratingNum * 5 % 10;
                for (var i = 0; i < ratingForNum; i++) {
                    // console.log(i == ratingForLastNum);
                    if (i == ratingForLastNum && ratingNumss <= 5 && ratingNum % 2 != 0) {
                        target.eq(i).append("<i class='half'></i>");
                    } else {
                        target.eq(i).append("<i></i>");
                    }
                }
                target.addClass("half-bg");

            }


            // var isWeixin = 1;

            if (!isWeixin) {
                //微信环境
                // $(".wx-warp").hide();
            }

            // 处理影片作者职位信息
            var authorIntroText = $(".new-art-author .intro").text();
            // authorIntroText = authorIntroText.replace(/,/," ");
            $(".new-art-author .intro").text(authorIntroText);

            var logObj = {
                "顶部视频点击播放数": "http://um0.cn/hIqli",
                "二视频点击播放数": "http://um0.cn/4rFEVC",
                "作者点击数": "http://um0.cn/2hwBE3",
                "相关推荐tab点击数": "http://um0.cn/oDGXH",
                "相关推荐视频点击数": "http://um0.cn/3fQSk3",
                "热门排行tab点击数": "http://um0.cn/1Qv5H",
                "热门排行视频点击数": "http://um0.cn/PR3ir",
                "“打开ZhuiXi查看更多讨论”点击数Ios": "https://at.umeng.com/LTLnOv",
                "“打开ZhuiXi查看更多讨论”点击数Android": "https://at.umeng.com/bqqKvu",
                "二维码长按次数": "http://um0.cn/1JdE8o",
                "底部fixed bar点击数": "http://um0.cn/3M1qYQ",
                "文章中间的引导下载button": "http://um0.cn/2VEdgM",
                "唤起app按钮Ios": "https://at.umeng.com/uOjGva",
                "唤起app按钮Android": "https://at.umeng.com/XHvK1n"
            }

            $(".log").on("click", function () {
                var img = new Image();
                // alert($(this).data('log'));
                if (!!$(this).data('logios') && isIOS) {
                    img.src = logObj[$(this).data('logios')];
                    // console.log(logObj[$(this).data('logios')])
                } else {
                    img.src = logObj[$(this).data('log')];
                    // console.log(logObj[$(this).data('log')])
                }
            })
        })
    </script>
    {{--<script type="text/javascript" src="/m-movie/a/weixin-sdk.js.下载"></script>--}}
    {{--<script type="text/javascript" src="/m-movie/a/WeixinApi.js.下载"></script>--}}
    {{--<script type="text/javascript">--}}
        {{--var articleDesc = "潜藏于陆地之下的世界，控制自身的超越之旅";--}}
        {{--wx.config({--}}
            {{--debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。--}}
            {{--appId: '', // 必填，公众号的唯一标识--}}
            {{--timestamp: 1479369043, // 必填，生成签名的时间戳--}}
            {{--nonceStr: 'GAdJBSRqMQF2LFne', // 必填，生成签名的随机串--}}
            {{--signature: '9715e34aded9634c3e894964ce51c72a951a39a6',// 必填，签名，见附录1--}}
            {{--jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] //  必填，需要使用的JS接口列表，所有JS接口列表见附录2--}}
        {{--});--}}
        {{--wx.ready(function () {--}}
            {{--wx.onMenuShareTimeline({--}}
                {{--title: "征服重力孤胆潜水纪录片《水下超人》 | ZhuiXi", // 分享标题--}}
                {{--link: 'http://www.zhuixi.com/50840?_vfrom=zhuixiApp_weixin', // 分享链接--}}
                {{--imgUrl: 'http://cs.vmoiver.com/Uploads/cover/2017-01-05/586db4eb96a0f_cut.jpeg', // 分享图标--}}
                {{--success: function () {--}}
                    {{--// 用户确认分享后执行的回调函数--}}
                {{--},--}}
                {{--cancel: function () {--}}
                    {{--// 用户取消分享后执行的回调函数--}}
                {{--}--}}
            {{--});--}}
            {{--wx.onMenuShareAppMessage({--}}
                {{--title: "征服重力孤胆潜水纪录片《水下超人》 | ZhuiXi", // 分享标题--}}
                {{--desc: articleDesc, // 分享描述--}}
                {{--link: 'http://www.zhuixi.com/50840?_vfrom=zhuixiApp_weixin', // 分享链接--}}
                {{--imgUrl: 'http://cs.vmoiver.com/Uploads/cover/2017-01-05/586db4eb96a0f_cut.jpeg', // 分享图标--}}
                {{--type: '', // 分享类型,music、video或link，不填默认为link--}}
                {{--dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空--}}
                {{--success: function () {--}}
                    {{--// 用户确认分享后执行的回调函数--}}
                {{--},--}}
                {{--cancel: function () {--}}
                    {{--// 用户取消分享后执行的回调函数--}}
                {{--}--}}
            {{--});--}}
        {{--})--}}
        {{--function connectWebViewJavascriptBridge(callback) {--}}
            {{--if (window.WebViewJavascriptBridge) {--}}
                {{--callback(WebViewJavascriptBridge)--}}
            {{--} else {--}}
                {{--document.addEventListener(--}}
                        {{--'WebViewJavascriptBridgeReady'--}}
                        {{--, function () {--}}
                            {{--callback(WebViewJavascriptBridge)--}}
                        {{--},--}}
                        {{--false--}}
                {{--);--}}
            {{--}--}}
        {{--}--}}
        {{--connectWebViewJavascriptBridge(function (bridge) {--}}
            {{--bridge.init(function (message, responseCallback) {--}}
                {{--console.log('JS got a message', message);--}}
                {{--var data = {--}}
                    {{--'Javascript Responds': 'Wee!'--}}
                {{--};--}}
                {{--console.log('JS responding with', data);--}}
                {{--responseCallback(data);--}}
            {{--});--}}


            {{--var shareData = {--}}
                {{--'sharetitle': "征服重力孤胆潜水纪录片《水下超人》 | ZhuiXi",--}}
                {{--'sharedefaultdes': articleDesc,--}}
                {{--'shareweibodes': articleDesc,--}}
                {{--'sharelink': 'http://www.zhuixi.com/50840?_vfrom=zhuixiApp_weixin',--}}
                {{--'shareimgurl': 'http://cs.vmoiver.com/Uploads/cover/2017-01-05/586db4eb96a0f_cut.jpeg'--}}
            {{--};--}}
            {{--window.WebViewJavascriptBridge.registerHandler("loadCompleteAction", function (data, responseCallback) {--}}
                {{--window.WebViewJavascriptBridge.callHandler('handlerShowShare', shareData, function (response) {--}}
                {{--});--}}
            {{--})--}}


        {{--})--}}
    {{--</script>--}}
    {{--<script type="text/javascript">--}}
        {{--var bds_config = {--}}
            {{--'bdPic': 'http://cs.vmoiver.com/Uploads/cover/2017-01-05/586db4eb96a0f_cut.jpeg',--}}
            {{--'snsKey': {--}}
                {{--'tsina': '447155826',--}}
                {{--'qzone': '125ed9a5daa55a184ee5f9e6b76ee752',--}}
                {{--'tqq': "100654608",--}}
                {{--'renren': 'fe0208a351224f7a8960ea6205a9558b'--}}
            {{--}--}}
        {{--};--}}
        {{--document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + new Date().getHours();--}}
    {{--</script>--}}
    {{--<script src="/m-movie/a/zhugeio.js.下载"></script>--}}
    {{--<script>--}}
        {{--// localStorage.clear();--}}
        {{--var userType = "";--}}
        {{--if (localStorage.getItem("userType")) {--}}
            {{--userType = localStorage.getItem("userType");--}}
            {{--if (userType == "B") {--}}
                {{--showPart();--}}
            {{--}--}}
        {{--}--}}
        {{--else {--}}
            {{--if (Math.random().toFixed(2) > 0.5) {--}}
                {{--showPart();--}}
                {{--userType = "B";--}}
            {{--}--}}
            {{--else {--}}
                {{--userType = "A";--}}
            {{--}--}}
            {{--localStorage.setItem("userType", userType);--}}
        {{--}--}}
        {{--$(document).on("click", ".zg", function () {--}}
            {{--zhuge.track($(this).data("zg"), {--}}
                {{--"来源": "wap",--}}
                {{--"用户类型": userType,--}}
                {{--"影片id": "50840",--}}
                {{--"影片名": "征服重力孤胆潜水纪录片《水下超人》",--}}
                {{--"影片分类": "运动",--}}
                {{--"影片时长(秒)": "180"--}}
            {{--});--}}
        {{--})--}}
        {{--function showPart() {--}}
            {{--$(".more-intro").on("click", function () {--}}
                {{--$(this).hide();--}}
                {{--$(".show-part").show();--}}
                {{--$(".new").hide();--}}
            {{--})--}}
            {{--var contentWrap = $(".new-art-content"),--}}
                    {{--content = contentWrap.text();--}}
            {{--contentWrap.after("<div class='new-art-content wx-art-content fs new mb0'><p class='short'>" + content + "</p></div>");--}}
            {{--$(".show-part").hide();--}}
            {{--$(".more-intro").show();--}}
        {{--}--}}
        {{--zhuge.track("ZhuiXi详情页wap" + userType + "测试进入");--}}
    {{--</script>--}}

{{--</div>--}}

{{--<!-- /主体 -->--}}

{{--<!-- 底部 -->--}}
{{--<script type="text/javascript" src="/m-movie/a/comment-mobile.js"></script>--}}
{{--<script type="text/javascript">--}}
    {{--var ThinkPHP;--}}
    {{--var loadingUserCheck = 0;--}}
    {{--// (function(){--}}
    {{--$.post('/User/userCheck', {}, function (res) {--}}
        {{--loadingUserCheck = 1;--}}
        {{--ThinkPHP = window.Think = {--}}
            {{--"ROOT": "", //当前网站地址--}}
            {{--"APP": "", //当前项目地址--}}
            {{--"PUBLIC": "/Public", //项目公共目录地址--}}
            {{--"CSS": "/Public/Home/css",--}}
            {{--"IMG": "/Public/Home/images",--}}
            {{--"XPC": "http://www.zhuixi.com",--}}
            {{--"DEEP": "/", //PATHINFO分割符--}}
            {{--"MODEL": ["2", "1", ""],--}}
            {{--"VAR": ["m", "c", "a"],--}}
            {{--"IS_LOGIN": res.userid,--}}
            {{--"LOGIN_USER": res.userinfo,--}}
        {{--}--}}

        {{--if (res.userid) {--}}
            {{--$('#user-pop').show();--}}
            {{--$('#user-login').hide();--}}

            {{--$('#user-image').attr('src', res.userinfo.avatar_180);--}}

            {{--$('#user-pop').css({'background-image': 'url(' + res.userinfo.avatar_180 + ')'});--}}
            {{--$('#user-image').after(res.userinfo.username);--}}
            {{--$('#my-like-post-num').text(res.likeNum);--}}
            {{--$('#my-like-album-num').text(res.albumNum);--}}

            {{--// 统计--}}
            {{--var unixTimestamp = Date.parse(new Date()) / 1000;--}}
            {{--if ($.cookie('v_checktime') == null || $.cookie('v_checktime') > unixTimestamp) {--}}
                {{--$.get('/user/userStatistic', {}, function (re) {--}}
                {{--}, 'json');--}}
            {{--}--}}
            {{--;--}}

            {{--$("#personal-post").attr('href', '/personal/' + res.userid + '/post');--}}
            {{--$("#personal-album").attr('href', '/personal/' + res.userid + '/album');--}}

            {{--$('.need-login').removeClass('need-login');--}}

            {{--setInterval(function () {--}}
                {{--getUnreadNum();--}}
            {{--}, 100000);--}}
            {{--getUnreadNum();--}}
            {{--if (res.SynLogin) {--}}
                {{--$(res.SynLogin).appendTo($('body'));--}}
            {{--}--}}
            {{--;--}}
            {{--// 用户画像--}}
            {{--var _dxt = _dxt || [];--}}
            {{--_dxt.push(["_setUserId", res.userid]);--}}
            {{--(function () {--}}
                {{--var hm = document.createElement("script");--}}
                {{--hm.src = "//datax.baidu.com/x.js?si=&dm=www.zhuixi.com";--}}
                {{--var s = document.getElementsByTagName("script")[0];--}}
                {{--s.parentNode.insertBefore(hm, s);--}}
            {{--})();--}}
        {{--} else {--}}
            {{--$(".login-mart").show();--}}
            {{--$('.normal-comment-mart').hide();--}}
            {{--$('.need-login').on('click', function () {--}}
                {{--location.href = "/login";--}}
            {{--});--}}
            {{--if (res.SynLogout) {--}}
                {{--$(res.SynLogout).appendTo($('body'));--}}
            {{--}--}}
            {{--;--}}
        {{--}--}}

        {{--if (postid) {--}}
            {{--$('#comment').show();--}}
            {{--if (res.userid != 0) {--}}
                {{--var str = '<a href="/personal/' + res.userid + '" class="img"><img src="' + ThinkPHP.LOGIN_USER.avatar_180 + '" ></a>' +--}}
                        {{--'<div class="comment-input"><textarea id="comment-textarea-re" class="comment-textarea" maxlength="2000" data-len="1" placeholder="你怎么看？"></textarea>' +--}}
                        {{--'<div class="comment-ope clearfix"><a href="javascript:;" class="comment-btn" data-referid="0" data-replyuid="0">发表点评</a>' +--}}
                        {{--'<span class="comment-tip">还可以输入<i>2000</i>个字</span>' +--}}
                        {{--'<div id="expression" class="expression expression-btn"></div></div></div>';--}}
            {{--} else {--}}
                {{--var str = '<a href="javascript:;" class="img"><img src="/Public/Home/images/avatar/a_180.jpeg" ></a><div class="comment-input"><div id="comment-textarea-re" class="comment-textarea need-login"></div><div class="comment-ope clearfix"><a href="javascript:;" class="comment-btn need-login" data-referid="0" data-replyuid="0">发表点评</a><span class="comment-tip">还可以输入<i>2000</i>个字</span><div id="expression" class="expression expression-btn need-login"></div></div></div>';--}}
            {{--}--}}
            {{--$(str).appendTo($('.comment-text'));--}}
            {{--//获取评论--}}
            {{--$.comment({login: Think.IS_LOGIN, postid: postid, type: commentType});--}}
        {{--}--}}
        {{--;--}}
    {{--}, 'json');--}}
    {{--var qingapp = getQueryString('qingapp');--}}
    {{--if (qingapp) {--}}
        {{--$.cookie('v_home_qingapp', qingapp);--}}
    {{--}--}}
    {{--;--}}

    {{--var isSafariFooter = (/iPhone/i.test(window.navigator.userAgent) || /iPod/i.test(window.navigator.userAgent) || /iPad/i.test(window.navigator.userAgent)) && !!window.navigator.userAgent.match(/(?:Safari\/)/) && !!window.navigator.userAgent.match(/(?:Version\/)/);--}}


    {{--if ($.cookie('v_home_qingapp')) {--}}
        {{--if ($.cookie('v_home_qingapp') == '360' && qingapp != '360') {--}}
            {{--$("#app-down-wrap").show();--}}
        {{--} else {--}}
            {{--$("#app-down-wrap").hide();--}}
        {{--}--}}
    {{--} else {--}}
        {{--$("#app-down-wrap").show();--}}
    {{--}--}}

    {{--if (qingapp == '360') {--}}
        {{--$("#user-login").hide();--}}
        {{--$("#wrap-pop").hide();--}}
    {{--}--}}
    {{--;--}}

    {{--if (isSafariFooter) {--}}

        {{--$("#app-down-wrap").hide();--}}
    {{--}--}}
    {{--function getQueryString(name) {--}}
        {{--var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");--}}
        {{--var r = window.location.search.substr(1).match(reg);--}}
        {{--if (r != null) return unescape(r[2]);--}}
        {{--return null;--}}
    {{--}--}}
    {{--function getUnreadNum() {--}}
        {{--$.post('/Notice/getUnreadNum', {}, function (res) {--}}
            {{--$('#msg-num').text(res.total);--}}
            {{--if (res.total) {--}}
                {{--$("#msg-tip").show();--}}
                {{--$("#msg-url").attr('href', res[0]['url']);--}}
            {{--}--}}
            {{--;--}}
        {{--}, 'json');--}}
    {{--}--}}
    {{--jQuery.cookie = function (name, value, options) {--}}
        {{--if (typeof value != 'undefined') { // name and value given, set cookie--}}
            {{--options = options || {};--}}
            {{--if (value === null) {--}}
                {{--value = '';--}}
                {{--options.expires = -1;--}}
            {{--}--}}
            {{--var expires = '';--}}
            {{--if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {--}}
                {{--var date;--}}
                {{--if (typeof options.expires == 'number') {--}}
                    {{--date = new Date();--}}
                    {{--date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));--}}
                {{--} else {--}}
                    {{--date = options.expires;--}}
                {{--}--}}
                {{--expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE--}}
            {{--}--}}
            {{--var path = options.path ? '; path=' + options.path : '';--}}
            {{--var domain = options.domain ? '; domain=' + options.domain : '';--}}
            {{--var secure = options.secure ? '; secure' : '';--}}
            {{--document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');--}}
        {{--} else { // only name given, get cookie--}}
            {{--var cookieValue = null;--}}
            {{--if (document.cookie && document.cookie != '') {--}}
                {{--var cookies = document.cookie.split(';');--}}
                {{--for (var i = 0; i < cookies.length; i++) {--}}
                    {{--var cookie = jQuery.trim(cookies[i]);--}}
                    {{--// Does this cookie string begin with the name we want?--}}
                    {{--if (cookie.substring(0, name.length + 1) == (name + '=')) {--}}
                        {{--cookieValue = decodeURIComponent(cookie.substring(name.length + 1));--}}
                        {{--break;--}}
                    {{--}--}}
                {{--}--}}
            {{--}--}}
            {{--return cookieValue;--}}
        {{--}--}}
    {{--};--}}
    {{--function getcookie(name) {--}}
        {{--var cookie_start = document.cookie.indexOf(name);--}}
        {{--var cookie_end = document.cookie.indexOf(";", cookie_start);--}}
        {{--return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));--}}
    {{--}--}}
    {{--function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {--}}
        {{--var expires = new Date();--}}
        {{--expires.setTime(expires.getTime() + seconds);--}}
        {{--document.cookie = escape(cookieName) + '=' + escape(cookieValue)--}}
                {{--+ (expires ? '; expires=' + expires.toGMTString() : '')--}}
                {{--+ (path ? '; path=' + path : '/')--}}
                {{--+ (domain ? '; domain=' + domain : '')--}}
                {{--+ (secure ? '; secure' : '');--}}
    {{--}--}}
{{--</script>--}}


{{--<!----}}
	{{--加载全站共有JS--}}
{{---->--}}


{{--<script>--}}
    {{--var _hmt = _hmt || [];--}}
    {{--(function () {--}}
        {{--var hm = document.createElement("script");--}}
        {{--hm.src = "//hm.baidu.com/hm.js?dfd9b1dd5fd1333a1c8e27ce6b5e60ce";--}}
        {{--var s = document.getElementsByTagName("script")[0];--}}
        {{--s.parentNode.insertBefore(hm, s);--}}
    {{--})();--}}
{{--</script>--}}


{{--<script type="text/javascript" src="/m-movie/a/weixin_common.js.下载"></script>--}}
{{--<script>--}}


    {{--$('#mobile-go-top').on('click', function () {--}}
        {{--$('body,html').animate({scrollTop: 0}, 400);--}}
    {{--});--}}
    {{--var url = "";--}}
    {{--var page = 1;--}}
    {{--var ing = 0;--}}
    {{--var pageSize = $(".re-content li").length;--}}
    {{--$(".more").click(function () {--}}
        {{--if (ing) {--}}
            {{--return--}}
        {{--}--}}
        {{--;--}}
        {{--ing = 1;--}}
        {{--page++;--}}
        {{--$(".more a").text('加载中');--}}
        {{--$.get(url, {p: page}, function (re) {--}}
            {{--if (re) {--}}
                {{--$(".re-content").append(re);--}}
            {{--} else {--}}
                {{--$(".more").hide();--}}
            {{--}--}}
            {{--ing = 0;--}}
            {{--if ($(".re-content li").length % pageSize) {--}}
                {{--$(".more").hide();--}}
            {{--} else {--}}
                {{--$(".more a").text('加载更多');--}}
            {{--}--}}

        {{--}, 'html');--}}

    {{--})--}}

    {{--$("#pc").click(function () {--}}
        {{--var regexp = new RegExp(/\?/);--}}
        {{--if (regexp.test(location.href)) {--}}
            {{--var href = location.href + '&pc=1';--}}
        {{--} else {--}}
            {{--var href = location.href + '?pc=1';--}}
        {{--}--}}
        {{--location.href = href;--}}
    {{--});--}}
    {{--function browser() {--}}
        {{--var u = navigator.userAgent.toLowerCase(), app = navigator.appVersion.toLowercase();--}}
        {{--return {--}}
            {{--ios: !!u.match(/\(i[^;]+;( u;)? cpu.+mac os x/),--}}
            {{--iPhone: u.indexOf('iphone') > -1,--}}
            {{--iPad: u.indexOf('ipad') > -1--}}
        {{--};--}}
    {{--}--}}
    {{--$(function () {--}}
        {{--var isSafari = (/iPhone/i.test(window.navigator.userAgent) || /iPod/i.test(window.navigator.userAgent) || /iPad/i.test(window.navigator.userAgent)) && !!navigator.appVersion.match(/(?:Safari\/)/);--}}
        {{--if (!isSafari) {--}}
            {{--// if( !$.cookie('hasDownApp')){--}}
            {{--// 	$('#app-down-wrap').hide();--}}
            {{--// }--}}
            {{--$('#app-down-wrap').on('click', '.app-close', function () {--}}
                {{--$('#app-down-wrap').addClass('dn');--}}
                {{--$.cookie('hasDownApp', 1);--}}
            {{--});--}}
            {{--$('.app-down-active').on('click', function () {--}}
                {{--var u = window.navigator.userAgent.toLowerCase();--}}
                {{--if (u.indexOf('iphone') > -1 || u.indexOf('ipad') > -1) {--}}
                    {{--window.location = "zhuixi:open";--}}
                    {{--setTimeout(function () {--}}
                        {{--window.location = "http://service.zhuixi.com/api/index/getApp?_vfrom=http%3A%2F%2Fwww.zhuixi.com%2Fwapheader&_vplatform=iphone";--}}
                    {{--}, 30);--}}
                {{--} else {--}}
                    {{--window.location = "http://service.zhuixi.com/api/index/getApp?_vfrom=http%3A%2F%2Fwww.zhuixi.com%2Fwapheader&_vplatform=android";--}}
                {{--}--}}
                {{--return false;--}}
            {{--});--}}
        {{--}--}}
    {{--});--}}


{{--</script>--}}


<!-- /底部 -->

</body>
</html>