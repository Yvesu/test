@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.video') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/video/index') }}">
                        {{ trans('content.normal').trans('common.video') }}
                    </a>
                </li>
            </ol>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">

        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <form action="/admin/video/index" method="get">
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
                            {{--<input type="hidden" name="active" value="{{ $request['active'] }}">--}}
                            {{--<input type="hidden" name="official" value="{{ $request['official'] }}">--}}
                            <input class="" type="text" id="base-material-text" name="search" placeholder="{{ trans('common.search') }}.." value="{{ $request['search'] }}">
                            {{ csrf_field() }}
                            <button class="input-group-addon" type="submit" style="box-shadow:none"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-1 text-center">ID</th>
                        <th class="col-md-2 text-center">{{ trans('content.thumbnail') }}</th>
                        <th class="col-md-3 text-center">{{ trans('content.content') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.duration') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.channel') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.visible') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.add_time') }}</th>
                        <th class="text-center col-md-1 col-xs-2 text-center">{{ trans('common.edit') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $data)
                        <tr class="divStatus">
                            <td class="text-center">{{ $data->id }}</td>
                            <div class="row items-push js-gallery">
                                <td class="font-w600">
                                    <a href="{{ CloudStorage::downloadUrl($data->video) }}" data-lightbox="iframe">
                                        <img class="img-responsive" src="{{ CloudStorage::downloadUrl($data->screen_shot).'?imageView2/2/h/100' }}">
                                    </a>
                                </td>
                            </div>
                            <td class="font-w600">{{ $data->hasOneContent['content'] }}</td>
                            <td class="hidden-xs">{{ $data->duration }}</td>
                            {{--<td class="hidden-xs">{{ $data->type == 0 ? '视频' : ($data->type == 1 ? '图片' : '网页') }}</td>--}}
                            <td class="text-center hidden-xs">{{ $data->channel_name }}</td>
                            <td class="hidden-xs">{{ $data->visible == 0 ? '公开' : '非公开' }}</td>
                            <td class="hidden-xs">{{ $data->created_at }}</td>
                            <td class="text-center videoCheck">
                                <a type="button" class="btn btn-default btn-xs"  data="{{ $data->id }}" href="{{ asset('/admin/video/details?id='.$data->id) }}">
                                    详情
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $datas->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>

            </div>
        </div>
    </div>
</div>

{{--视频播放专用--}}
<link rel="stylesheet" href="{{ asset('/css/admin/video/video.css') }}" type="text/css" />
<script type="text/javascript" src="{{ asset('/js/admin/video/plugins.js') }}"></script>
<script type="text/javascript" src="{{ asset('/js/admin/video/functions.js') }}"></script>
<script type="text/javascript" src="{{ asset('/js/layer/layer.js') }}"></script>
<script type="text/javascript" src="{{ asset('/js/admin/video/video-handle.js') }}"></script>

@endsection
