@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.reply') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/reply/index') }}">{{trans('common.reply_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.reply_management')}}
            </h1>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt reply-status">
            <li class="{{ isActiveLabel('active',1,'active','') }}" name="reply-normal">
                <a href="{{ asset('/admin/reply/index?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',2,'active','') }}" name="reply-shield">
                <a href="{{ asset('/admin/reply/index?active=2') }}">{{ trans('common.shield') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="/admin/reply/index" method="get">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div id="DataTables_Table_1_length" class="dataTables_length">
                            <!-- 每页显示页码选择 -->
                            <label>显示
                                <select name="num" size="1" aria-controls="DataTables_Table_1">
                                    <!-- 每页显示条数 -->
                                    @for ($i = 10; $i < 120; $i=$i*2)
                                        @if ($request['num'] == $i)
                                            <option value="{{ $i }}" selected>{{ $i }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endif
                                    @endfor
                                </select>条数据
                            </label>
                        </div>
                    </div>
                    <!-- 搜索 -->
                    <div class="col-sm-6 col-md-4 pull-right" style="margin-bottom: 10px;">
                        <div id="DataTables_Table_1_length" class="dataTables_length col-md-4">
                            <!-- 搜索条件 -->
                            <label>
                                <select name="condition" size="1" aria-controls="DataTables_Table_1">
                                    @for ($i = 1; $i <= count($condition); $i++)
                                        @if ($request['condition'] == $i)
                                            <option value="{{ $i }}" selected>{{ $condition[$i] }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $condition[$i] }}</option>
                                        @endif
                                    @endfor
                                </select>
                            </label>
                        </div>
                        <div class="form-material form-material-primary input-group remove-margin-t remove-margin-b col-md-8">
                            <input type="hidden" name="active" value="{{ $request['active'] }}">
                            <input class="" type="text" id="base-material-text" name="search" placeholder="{{ trans('common.search') }}.." value="{{ $request['search'] }}">
                            {{ csrf_field() }}
                            <button class="input-group-addon" type="submit" style="box-shadow:none"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    @if($datas->count())
                        <thead>
                        <tr>
                            <th class="col-md-1 text-center">{{ trans('content.number') }}</th>
                            <th class="hidden-xs col-md-1 text-center">{{ trans('content.unofficial') }}</th>
                            <th class="hidden-xs col-md-2 text-center">{{ trans('content.reply_time') }}</th>
                            <th class="hidden-xs col-md-2 text-center">{{ trans('content.like_count') }}</th>
                            <th class="col-md-4 text-center">{{ trans('content.content') }}</th>
                            <th class="col-md-1 text-center">{{ trans('common.edit') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($datas as $data)
                            <tr id="{{ $data->id }}">
                                <td class="text-center">{{ $data->id }}</td>
                                <td class="hidden-xs text-center">{{ $data->user_id }}</td>
                                <td class="hidden-xs text-center">{{ $data -> created_at }}</td>
                                <td class="hidden-xs text-center">{{ $data -> like_count }}</td>
                                <td class="hidden-xs text-center">{{ $data -> content }}</td>
                                <td class="text-center">
                                    @if($data -> status === 0)
                                        <button class="btn btn-sm bg-red reply_shield" onclick="return false" value="{{ $data->id }}">{{ trans('common.shield') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    @endif
                </table>
                @if(!$datas->count())
                    <div class="col-md-12 text-center" style="margin-bottom: 60px">{{ trans('content.nothing') }}</div>
                @endif
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $datas->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('/js/layer/layer.js')  }}" type="text/javascript" charset="utf-8"></script>

<script>
    $(function(){

        // 点击事件
        $('.reply_shield').click(function(){

            var id = $(this).attr('value');
            var name = $('.reply-status>li[class=active]').attr('name');
            // 发送ajax
            $.get('/admin/reply/ajax',{
                id:id
            },function(data) {
                if(data.status == 1) {
                    layer.msg('操作成功');
                    if(name == 'reply-normal' || name == 'reply-wait'){
                        $('tr[id='+id+']').remove();
                    }else{
                        location.reload();
                    }

                }else{
                    layer.msg('操作失败');
                }

            },'json');
        });

    });

</script>

@endsection
