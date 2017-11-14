@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.agreement') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/agreement/index') }}">{{ trans('common.agreement').trans('common.management') }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.agreement').trans('common.management') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/agreement/add') }}">
                    {{ trans('common.add') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="block-content tab-content">
            {{--搜索--}}
            <form action="/admin/agreement/index" method="get">
                <div class="col-sm-6 col-md-4 hidden-xs">
                    <div id="DataTables_Table_1_length" class="dataTables_length">
                        <!-- 每页显示页码选择 -->
                        <label>{{ trans('content.show_nav') }}
                            <select name="num" size="1" aria-controls="DataTables_Table_1">
                                <!-- 每页显示条数 -->
                                @for ($i = 10; $i < 120; $i=$i*2)
                                    @if ($request['num'] == $i)
                                        <option value="{{ $i }}" selected>{{ $i }}</option>
                                    @else
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endif
                                @endfor
                            </select>{{ trans('content.branches') }}
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
                        <input class="" type="text" id="base-material-text" name="search" placeholder="{{ trans('common.search') }}.." value="{{ $request['search'] }}">
                        {{ csrf_field() }}
                        <button class="input-group-addon" type="submit" style="box-shadow:none"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered table-striped js-dataTable-full">
                <thead>
                <tr>
                    <th class="col-md-1 text-center">ID</th>
                    <th class="col-md-2">{{ trans('content.name') }}</th>
                    <th class="hidden-xs col-md-1">{{ trans('common.active') }}</th>
                    <th class="hidden-xs col-md-1">{{ trans('content.add_time') }}</th>
                    <th class="hidden-xs col-md-1">{{ trans('content.update_time') }}</th>
                    <th class="text-center col-md-1 col-xs-2">{{ trans('common.detail_info') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($datas as $k=>$data)
                    <tr class="{{ ($k%2==1)?'odd':'even' }}">
                        <td class="text-center">{{ $data->id }}</td>
                        <td class="font-w600">{{ $data->title }}</td>
                        <td class="hidden-xs">{{ $data->active == 1 ? '已生效' : '已注销' }}</td>
                        <td class="hidden-xs">{{ date('Y-m-d H:i',$data->time_add) }}</td>
                        <td class="hidden-xs">{{ date('Y-m-d H:i',$data->time_update) }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" title="详情" href="{{ asset('/admin/agreement/details?id='.$data->id) }}"><i class="fa fa-align-justify"></i></a>
                            </div>
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" href="/admin/agreement/delete?id={{ $data->id }}" title="删除"><i class="fa fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <!-- 分页 -->
            <div class="dataTables_paginate paging_full_numbers" id="pages">
                {!! $datas->appends($request)->render() !!}
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
            $.get('/admin/agreement/ajax',{
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
