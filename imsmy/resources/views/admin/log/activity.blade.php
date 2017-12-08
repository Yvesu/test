@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->

<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.log') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/log/activity') }}">
                        {{ trans('content.activity').trans('common.manage_log') }}
                    </a>
                </li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('content.activity').trans('common.manage_log') }}
            </h1>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="block-content tab-content">
            <div class="col-md-12 col-xs-12 block">
                {{--搜索--}}
                <form action="/admin/log/activity" method="get">
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
                                </select> {{ trans('content.branches') }}
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
                    @if($datas->count())
                        <thead>
                        <tr>
                            <th class="col-md-2 text-center">{{ trans('content.activity') }}ID</th>
                            <th class="col-md-2 text-center">{{ trans('content.activity_name') }}</th>
                            <th class="col-md-2 text-center">{{ trans('content.handle') }}</th>
                            <th class="col-md-2 text-center">{{ trans('content.complain_result') }}</th>
                            <th class="col-md-2 text-center">{{ trans('content.complain_time') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($datas as $k=>$data)
                            <tr>
                                <td class="text-center">{{ $data->data_id }}</td>
                                <td class="text-center">{{ $data->activity_name }}</td>
                                <td class="text-center">{{ $data->user_name }}</td>
                                <td class="text-center">{{ $data->active == 1 ? trans('content.normal') : trans('common.shield') }}</td>
                                <td class="text-center">{{ date('Y-m-d H:i:s',$data->time_add) }}</td>
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

@endsection
