@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.file') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/file/index') }}">{{ trans('common.file').trans('common.management') }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">

        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/file/add') }}">
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
            <div class="block-content tab-content">
                <form action="/admin/file/index" method="get">
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
                        <th class="col-md-2 text-center">{{ trans('common.title') }}</th>
                        <th class="col-md-6 text-center">{{ trans('common.url') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.add_time') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.update_time') }}</th>
                        <th class="text-center col-md-1 text-center">{{ trans('common.edit') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $value)
                        <tr class="divStatus">
                            <td class="text-center">{{ $value->id }}</td>
                            <td class="font-w600">{{ $value->name }}</td>
                            <td class="hidden-xs">{{ CloudStorage::downloadUrl($value->url) }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i:s',$value -> time_add) }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i:s',$value -> time_update) }}</td>
                            <td class="text-center videoCheck">
                                <button type="button" class="btn btn-danger btn-xs" status="3" title="{{ trans('common.delete') }}" value="{{ $value->id }}" onclick="_fileEdit($(this))">
                                    <i class="fa fa-remove"></i>
                                </button>
                                <a type="button" class="btn btn-default btn-xs"  target="_blank" title="{{ trans('common.details') }}" href="{{ CloudStorage::downloadUrl($value->url) }}">
                                    {{ trans('common.details') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(!$data->count())
                    <div class="col-md-12 col-md-offset-5" style="margin-bottom: 30px;">暂无数据</div>
                @endif
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $data->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>

            </div>
        </div>
    </div>
</div>

<script src="{{ asset('/js/layer/layer.js')  }}" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="{{ asset('/js/admin/video/video-handle.js') }}"></script>

@endsection
