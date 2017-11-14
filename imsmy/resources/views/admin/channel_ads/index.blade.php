@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.advertisement') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/advertisement/channel/index') }}">{{trans('common.ads_channel')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.ads_channel')}}
            </h1>
        </div>
        @if($user->user_id !== null)
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn btn-primary" href="{{ asset('/admin/advertisement/channel/add') }}">
                        {{ trans('content.add_ads_channel') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isStyleLabel('style',1,'active','') }}">
                <a href="{{ asset('/admin/advertisement/channel/index?style=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',0,'active','') }}">
                <a href="{{ asset('/admin/advertisement/channel/index?style=0') }}">{{ trans('content.wait') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',2,'active','') }}">
                <a href="{{ asset('/admin/advertisement/channel/index?style=2') }}">{{ trans('content.overdue') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',3,'active','') }}">
                <a href="{{ asset('/admin/advertisement/channel/index?style=3') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="/admin/advertisement/channel/index" method="get">
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
                            <input type="hidden" name="style" value="{{ $request['style'] }}">
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
                        <th class="col-md-1 text-center">{{ trans('content.number') }}</th>
                        <th class="col-md-1 text-center">{{ trans('content.submitter') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.name') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.ads_type') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.from_time') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.end_time') }}</th>
                        <th class="col-md-2 col-xs-3 text-center">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ads as $ad)
                        <tr>
                            <td class="text-center">{{ $ad->id }}</td>
                            <td class="text-center">{{ $ad->user_name }}</td>
                            <td class="hidden-xs text-center">{{ $ad->name }}</td>
                            <td class="hidden-xs text-center">{{ $ad -> type }}</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$ad -> from_time) }}</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$ad -> end_time) }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" title="详情/编辑" href="{{ asset('/admin/advertisement/channel/details?type_id=' . $ad->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $ads->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection
