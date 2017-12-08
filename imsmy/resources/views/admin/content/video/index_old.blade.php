@extends('admin/layer')
@section('layer-content')
<!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/video') }}">{{trans('common.video')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.video')}}
            </h1>
        </div>
        @if($user->user_id !== null)
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/video/create') }}">
                        {{ trans('content.add_video') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">

        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->


                <div class="col-md-12 col-sm-12 col-xs-12 borderR">

                    <div class="row mod-content posi-rela">
                        <!--栏目导航 Start-->
                        <ul class="nav nav-tabs row" role="tablist" id="nav_menu">
                            <li role="presentation" class="active col-sm-1 col-xs-2">
                                <a href="#recommend" role="tab" data-toggle="tab">推荐</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#movie" role="tab" data-toggle="tab">电影</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#star" role="tab" data-toggle="tab">明星</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#tourism" role="tab" data-toggle="tab">旅游</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#delicacy" role="tab" data-toggle="tab">美食</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#music" role="tab" data-toggle="tab">音乐</a>
                            </li>
                            <li role="presentation" class="col-sm-1 col-xs-2">
                                <a href="#dance" role="tab" data-toggle="tab">热舞</a>
                            </li>
                        </ul>
                        <!--栏目导航 End-->

                        {{--<div class="row push-30-t">--}}


                            {{--<div class="col-xs-3" >--}}
                                {{--<div>--}}
                                    {{--真实代码--}}
                                    {{--<video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls--}}
                                    {{--preload="auto"  poster="{{ CloudStorage::downloadUrl($video->screen_shot) }}"--}}
                                    {{--data-setup="{}" webkit-playsinline>--}}

                                    {{--测试代码--}}
                                    {{--<video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls--}}
                                           {{--preload="auto"  poster="{{asset('/web/57d0d1042a7ec_1024.jpg')}}"--}}
                                           {{--data-setup="{}" webkit-playsinline>--}}

                                        {{--真实代码--}}
                                        {{--<source src="{{ CloudStorage::downloadUrl($video->video) }}" type="video/mp4">--}}

                                        {{--测试代码--}}
                                        {{--<source src="{{asset('/web/57d0d0e5357c5.mp4')}}" type="video/mp4">--}}

                                    {{--</video>--}}
                                {{--</div>--}}
                                {{--<div class="col-xs-12" style="margin-top: 5px;">--}}
                                    {{--<div class="col-sm-6">--}}
                                        {{--<button class="btn btn-minw btn-rounded btn-danger" type="button">屏蔽</button>--}}
                                    {{--</div>--}}
                                    {{--<div class="col-sm-6">--}}
                                        {{--<button class="btn btn-minw btn-rounded btn-success" type="button">通过</button>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                        {{--</div>--}}

                        <!-- 查看更多标签 Start -->
                        <div class="tab-content" >
                            <div role="tabpanel" class="tab-pane active _pane" id="recommend">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 more" id="">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane _pane" id="movie">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane _pane" id="star">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane _pane" id="tourism">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane  _pane" id="delicacy">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane  _pane" id="music">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                            <div role="tabpanel" class="tab-pane  _pane" id="dance">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12 more">查看更多</button>
                            </div>
                        </div>
                        <!-- 查看更多标签 End -->
                        <br>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="{{ asset('/js/admin/loadInfo.js') }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ asset('/js/jquery-1.8.3.min.js') }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ asset('/js/layer/layer.js')  }}" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){

        $('#recommend .more').click(function(){
            loadInfo(1);		//推荐加载
        });
        $('#movie .more').click(function(){
            loadInfo(2);		//电影加载
        });
        $('#star .more').click(function(){
            loadInfo(3);		//明星加载
        });
        $('#tourism .more').click(function(){
            loadInfo(4);	    //旅游加载
        });
        $('#delicacy .more').click(function(){
            loadInfo(5);	    //美食加载
        });
        $('#music .more').click(function(){
            loadInfo(6);	    //音乐加载
        });
        $('#dance .more').click(function(){
            loadInfo(7);	    //热舞加载
        });


        //初始加载各数据
        $('.tab-pane._pane>.more').click();

        //标签页添加、清除 active
        $('#nav_menu li[role=presentation]').click(function(){
            $('#nav_menu li[role=presentation]').each(function(){
                $(this).removeClass('active');
                $('#nav_menu').next().find('.tab-pane._pane').hide();
            });
            $(this).addClass('active');
            var id = $(this).find('a').first().attr('href');
            $('#nav_menu').next().find(id).show();
        });

    });

</script>
@endsection
