@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/photo_album') }}">{{trans('common.photo_album')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.photo_album')}}
            </h1>
        </div>
        @if($user->user_id !== null)
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/photo_album/create') }}">
                        {{ trans('content.add_photo_album') }}
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
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('admin/content/photo_album?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',1,'active','') }}">
                <a href="{{ asset('admin/content/photo_album?official=1') }}">{{ trans('content.official') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',0,'active','') }}">
                <a href="{{ asset('admin/content/photo_album?official=0') }}">{{ trans('content.unofficial') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('admin/content/photo_album?active=0') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="co-md-2 text-center"></th>
                        <th class="col-md-2">{{ trans('content.sponsor') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.retweet') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.comment') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.like') }}</th>
                        <th class="col-md-2 col-xs-3 text-center">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tweets as $tweet)
                        <tr>
                            <td class="text-center">{{ $tweet->id }}</td>
                            <td class="font-w600">{{ $tweet->user_id }}</td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" href="{{ asset('/admin/content/photo_album/' . $tweet->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(\Request::get('active') !== null)
                    {!! (new \App\Services\Presenter($tweets->appends(['active' => \Request::get('active')])))->render() !!}
                @elseif(\Request::get('official') !== null)
                    {!! (new \App\Services\Presenter($tweets->appends(['official' => \Request::get('official')])))->render() !!}
                @endif
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection
