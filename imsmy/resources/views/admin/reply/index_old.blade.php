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
            <li class="{{ isStyleLabel('style',1,'active','') }}" name="reply-already">
                <a href="{{ asset('/admin/reply/index?style=1') }}">{{ trans('content.already') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',0,'active','') }}" name="reply-wait">
                <a href="{{ asset('/admin/reply/index?style=0') }}">{{ trans('content.wait') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',2,'active','') }}" name="reply-normal">
                <a href="{{ asset('/admin/reply/index?style=2') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',3,'active','') }}" name="reply-shield">
                <a href="{{ asset('/admin/reply/index?style=3') }}">{{ trans('common.shield') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    @if($replies->count())
                        <thead>
                        <tr>
                            <th class="col-md-1 text-center">{{ trans('content.number') }}</th>
                            <th class="hidden-xs col-md-1 text-center">{{ trans('content.unofficial') }}</th>
                            <th class="hidden-xs col-md-2 text-center">{{ trans('content.reply_time') }}</th>
                            @if($replies[0] -> active === 1)
                                <th class="hidden-xs col-md-3 text-center">{{ trans('content.content') }}</th>
                                <th class="hidden-xs col-md-1 text-center">{{ trans('content.handle') }}</th>
                                <th class="hidden-xs col-md-2 text-center">{{ trans('content.complain_time') }}</th>
                                <th class="hidden-xs col-md-1 text-center">{{ trans('content.complain_result') }}</th>
                            @else
                                <th class="hidden-xs col-md-7 text-center">{{ trans('content.content') }}</th>
                            @endif
                            <th class="col-md-1 text-center">{{ trans('common.edit') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($replies as $reply)
                            <tr id="{{ $reply->reply_id }}">
                                <td class="text-center">{{ $reply->id }}</td>
                                <td class="hidden-xs text-center">{{ $reply->belongsToReply->user_id }}</td>
                                <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$reply -> time_add) }}</td>
                                @if($reply -> active === 1)
                                    <td class="hidden-xs text-center">{{ $reply -> belongsToReply -> content }}</td>
                                    <td class="hidden-xs text-center">{{ $reply -> active === 1 ? $reply -> user_name : '尚未处理' }}</td>
                                    <td class="hidden-xs text-center">{{ $reply -> time_update ? date('Y-m-d H:i:s',$reply -> time_update) : '尚未处理' }}</td>
                                    <td class="hidden-xs text-center" id="reply_shield">{{ $reply -> active === 0 ? '尚未处理' : ($reply -> belongsToReply -> status === 0 ? '正常' : '屏蔽') }}</td>
                                @else
                                    <td class="hidden-xs text-center">{{ $reply -> belongsToReply -> content }}</td>
                                @endif
                                <td class="text-center">
                                    @if($reply -> belongsToReply -> status === 0)
                                        <button class="btn btn-sm bg-red reply_shield" onclick="return false" value="{{ $reply->reply_id }}">{{ trans('common.shield') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    @endif
                </table>
                @if(!$replies->count())
                    <div class="col-md-12 text-center" style="margin-bottom: 60px">{{ trans('content.nothing') }}</div>
                @endif
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $replies->appends($request)->render() !!}
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
