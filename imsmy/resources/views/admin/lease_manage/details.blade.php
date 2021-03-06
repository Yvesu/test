@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.lease') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/lease/index') }}">{{trans('common.lease_management')}}</a></li>
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
                <form action="{{ asset('/admin/lease/update?id=' . $data->id) }}" method="POST">
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
                        {{ trans('content.sponsor').' : ' . $data->user_id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.lease_type').' : ' . $data->hasOneType['name'] }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.cost') . ' : ' . $data->cost }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.cost_type') . ' : ' . $data->cost_type }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.ad') . ' : ' . $data->ad }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.ad_details') . ' : ' . $data->ad_details }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.add_time') . ' : ' . date('Y-m-d H:i:s',$data->time_add) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.update_time') . ' : ' . date('Y-m-d H:i:s',$data->time_update) }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.intro') . ' : ' . $data->hasOneIntro['intro'] }}
                    </div>
                </div>

                @if (sizeof($data->photos))
                    <div class="row push-30-t">
                        @foreach($data->photos as $photo)
                            <div class="col-xs-4 remove-padding">
                                <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($photo) }}">
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    @parent
    <link href="https://cdn.bootcss.com/video.js/5.10.2/video-js.min.css" rel="stylesheet">
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.bootcss.com/video.js/5.10.2/video.min.js"></script>
    <script>
        videojs.options.flash.swf = "{{ asset('js/plugins/videojs/video-js.swf')  }}";
    </script>
@endsection