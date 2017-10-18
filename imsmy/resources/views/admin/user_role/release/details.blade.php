@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.user_role') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/user/role/index') }}">{{trans('common.user_role_management')}}</a></li>
                <li>{{ $data->id }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            {{--<h1 class="page-heading" >--}}
                {{--{{ trans('content.topic'). ' : ' .$topic->name}}--}}
            {{--</h1>--}}
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/user/role/update?id=' . $data->id) }}" method="POST">
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(1 != $data->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                    @else
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="col-xs-12">
    <div class="block-content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ 'ID : ' . $data->id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.sponsor').' : ' . $data->user_id }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.film_title') . ' : ' . $data->title }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.director') . ' : ' . $data->director }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.film_name') . ' : ' . $data->hasOneFilm['name'] }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.period') . ' : ' . $data->period }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.from_time') . ' : ' . $data->time_from }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.end_time') . ' : ' . $data->time_end }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.submit_time') . ' : ' . date('Y-m-d H:i:s',$data->time_add) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.update_time') . ' : ' . date('Y-m-d H:i:s',$data->time_update) }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.film_intro') . ' : ' . $data->hasOneIntro['intro'] }}
                    </div>
                </div>

                @foreach($data->hasManyDetails as $key => $value)
                    <div class="row push-30-t">
                        <div class="col-xs-12">
                            {{ trans('common.user_role') .' '. ++$key . ' : ' }}
                        </div>
                    </div>
                    <div class="row push-30-t col-md-offset-1">
                        <div class="col-xs-6">
                            {{ trans('content.role_name') . ' : ' . $value->name }}
                        </div>
                        <div class="col-xs-6">
                            {{ trans('content.role_age') . ' : ' . $value->age }}
                        </div>
                    </div>
                    <div class="row push-30-t col-md-offset-1">
                        <div class="col-xs-6">
                            {{ trans('content.role_type') . ' : ' . $value->belongsToType['name'] }}
                        </div>
                    </div>
                    <div class="row push-30-t col-md-offset-1">
                        <div class="col-xs-12">
                            {{ trans('content.role_biography') . ' : ' . $value->hasOneBiography['intro'] }}
                        </div>
                    </div>
                @endforeach

                @foreach($data->hasManyAudition as $key => $value)
                    <div class="row push-30-t">
                        <div class="col-xs-12">
                            {{ trans('common.user_role_audition') .' '. ++$key . ' : ' }}
                        </div>
                    </div>
                    <div class="row push-30-t col-md-offset-1">
                        <div class="col-xs-12">
                            {{ trans('common.requirements') . ' : ' . $value->content }}
                        </div>
                    </div>
                @endforeach

                @if (sizeof($data->cover))
                    <div class="row push-30-t">
                        <div class="col-xs-12">
                            {{ trans('content.film_cover') . ' : ' }}
                        </div>
                        <div class="col-xs-6 remove-padding col-md-offset-1">
                            <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($data->cover) }}">
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
