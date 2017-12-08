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
                                <a href="#notdefined" role="tab" data-toggle="tab">未归类</a>
                            </li>
                        </ul>
                        <!--栏目导航 End-->

                        <!-- 查看更多标签 Start -->
                        <div class="tab-content" >
                            <div role="tabpanel" class="tab-pane active _pane" id="notdefined">
                                <div class="content"> </div>
                                <button class="btn btn-default col-md-4 col-md-offset-4 more" id="">查看更多</button>
                            </div>
                        </div>
                        <!-- 查看更多标签 End -->

                    </div>
                </div>

            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ URL('admin/content/ajax/choose') }}" method="post">
                        {{ csrf_field() }}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h5 class="modal-title" id="myModalLabel">选择频道</h5>
                        </div>
                        <div class="modal-body">
                            <select class="form-control" name='channel_id'>
                                @foreach($channel as $k=>$v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{--<div class="modal-footer" >--}}
                            {{--<button type="button" class=" btn btn-default" data-dismiss="modal">取消</button>--}}
                            {{--<input type="submit" class="btn btn-success" id="submit-button" value="" onclick="return false" value="提交">--}}
                        {{--</div>--}}
                        <div class="modal-footer" >
                            <button type="button" class=" btn btn-default" data-dismiss="modal">取消</button>
                            <input type="submit" class="btn btn-success"  value="提交">
                            <input type="hidden" id="submit-button" name="video" value="">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal End -->

    </div>
</div>

<script src="{{ asset('/js/admin/loadInfo.js') }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ asset('/js/layer/layer.js')  }}" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){

        $('#notdefined .more').click(function(){
            loadCheck();		//推荐加载
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
