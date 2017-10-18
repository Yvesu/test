@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.chartlet').trans('common.file') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/make/chartlet/file/index') }}">
                        {{ trans('common.chartlet').trans('common.file').trans('common.management') }}
                    </a>
                </li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.chartlet').trans('common.file').trans('common.management') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/make/chartlet/file/add') }}">
                    {{ trans('common.add').trans('common.chartlet').trans('common.file') }}
                </a>
            </div>
        </div>
    </div>
</div>
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('/admin/make/chartlet/file/index?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',3,'active','') }}">
                <a href="{{ asset('/admin/make/chartlet/file/index?active=3') }}">{{ trans('content.recommend') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('/admin/make/chartlet/file/index?active=0') }}">{{ trans('content.wait') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',2,'active','') }}">
                <a href="{{ asset('/admin/make/chartlet/file/index?active=2') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="{{ asset('/admin/make/chartlet/file/index') }}" method="get">
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
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-1 text-center">ID</th>
                        <th class="col-md-2 text-center">{{ trans('content.thumbnail') }}</th>
                        <th class="col-md-1 text-center">{{ trans('content.name') }}</th>
                        <th class="col-md-2 text-center">{{ trans('content.intro') }}</th>
                        <th class="col-md-1 text-center">{{ trans('common.folder') }}</th>
                        <th class="col-md-1 text-center">{{ trans('content.duration') }}</th>
                        <th class="col-md-1 text-center">{{ trans('content.file_size') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.add_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.update_time') }}</th>
                        <th class="text-center col-md-1">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $data)
                        <tr class="divStatus">
                            <td class="text-center">{{ $data->id }}</td>
                            <div class="row items-push js-gallery">
                                <td class="font-w600">
                                    <a href="{{ CloudStorage::downloadUrl($data->preview_address) }}" data-lightbox="iframe">
                                        <img class="img-responsive" src="{{ CloudStorage::downloadUrl($data->cover).'?imageView2/2/h/100' }}">
                                    </a>
                                </td>
                            </div>
                            <td class="font-w600 text-center">{{ $data->name }}</td>
                            <td class="font-w600 text-center">{{ $data->intro }}</td>
                            <td class="font-w600 text-center">{{ $folder[$data->folder_id] }}</td>
                            <td class="font-w600 text-center">{{ $data->duration }}</td>
                            <td class="font-w600 text-center">{{ $data->size }}k</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i:s',$data->time_add) }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i:s',$data->time_update) }}</td>
                            <td class="text-center">
                                {{--<div class="btn-group">--}}
                                    {{--<a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/make/chartlet/file/edit?id='.$data->id) }}"><i class="fa fa-pencil"></i></a>--}}
                                {{--</div>--}}
                                @if($data -> active == 0)
                                    <button type="button" class="btn btn-danger btn-xs" status="4" title="激活" value="{{ $data->id }}" onclick="_chartletFileSort($(this))">
                                        <i class="fa fa-check"></i>
                                    </button>
                                @endif
                                @if($data -> active == 1)
                                    <button type="button" class="btn btn-success btn-xs" status="5" title="{{ 1 == $data->recommend ? '取消推荐' : '推荐' }}" value="{{ $data->id }}" onclick="_chartletFileSort($(this))">
                                        <i class="glyphicon {{ 1 == $data->recommend ? 'glyphicon-thumbs-down' : 'glyphicon-thumbs-up' }}"></i>
                                    </button>
                                    @if($request['active'] == 3)
                                        <button type="button" class="btn btn-success btn-xs" status="1" title="上移" value="{{ $data->id }}" onclick="_chartletFileSort($(this))">
                                            <i class="glyphicon glyphicon-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-xs" status="2" title="下移" value="{{ $data->id }}" onclick="_chartletFileSort($(this))">
                                            <i class="glyphicon glyphicon-arrow-down"></i>
                                        </button>
                                    @endif
                                @endif
                                <button type="button" class="btn btn-danger btn-xs" status="3" title="删除" value="{{ $data->id }}" onclick="_chartletFileSort($(this))">
                                    <i class="fa fa-remove"></i>
                                </button>
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
{{--<script type="text/javascript" src="{{ asset('/js/admin/video/video-handle.js') }}"></script>--}}
<script type="text/javascript" src="{{ asset('/js/admin/user-role/sort.js') }}"></script>
@endsection
