@extends('admin.layer')
@section('layer-content')
<!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="#">{{trans('common.push_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.push_content')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/push/create') }}">
                    {{ trans('content.add_push') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('admin/content/push?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('admin/content/push?active=0') }}">{{ trans('content.wait') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',2,'active','') }}">
                <a href="{{ asset('admin/content/push?active=2') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-2 text-center">{{ trans('content.video_number') }}</th>
                        <th class="col-md-2 text-center">{{ trans('content.push_date') }}</th>
                        <th class="text-center col-md-2 col-xs-3">{{ trans('common.edit') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($pushes as $push)
                            <tr>
                                <td class="text-center">{{ $push->tweet_id }}</td>
                                <td class="text-center">{{ $push->date }}</td>
                                <td class="text-center">
                                    <form action="{{ asset('/admin/content/push/'. $push->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PUT">
                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                        <input type="hidden" name="active" value="{{ $push -> active == 0 ? 1 : ($push -> active == 1 ? 2 : 1)  }}">
                                        <div class="btn-group">
                                            <a class="btn btn-xs btn-default" title="{{ trans('common.edit') }}" href="{{ asset('/admin/content/video/' . $push->tweet_id)}}"><i class="fa fa-pencil"></i> {{ trans('common.details') }}</a>
                                        </div>
                                        <button class="btn btn-sm bg-red" type="submit">
                                             {{ trans($push -> active == 0 ? 'common.pass' : ($push -> active == 1 ? 'common.disable' : 'common.enable')) }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $pushes->render() !!}
                </div>
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection