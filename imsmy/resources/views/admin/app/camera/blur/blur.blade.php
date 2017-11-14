@extends('admin.layer')
@section('layer-content')
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.camera') }}</li>
                <li><a class="link-effect" href="">{{trans('common.blur')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.blur')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ trans('management.menu') }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu bg-yellow-lighter">
                    <li><a href="{{ asset('/admin/app/camera/blur/class') }}" >{{ trans('app.add_class') }}</a></li>
                    <li><a href="{{ asset('/admin/app/camera/blur/class/management') }}" >{{ trans('app.class_management') }}</a></li>
                    <li><a href="{{ asset('/admin/app/camera/blur/add') }}" >{{ trans('app.add_blur') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
    <!-- END Page Header -->
    <div class="col-xs-12">
        <div class="block-content">
            <h4 class="blur-nav">
                <a class="label {{ isActiveLabel('active',1) }}" href="{{ asset('admin/app/camera/blur?active=1') }}"><i class="fa fa-play"></i>&nbsp;&nbsp;{{ trans('app.normal') }}</a>
                <a class="label {{ isActiveLabel('active',0) }}" href="{{ asset('admin/app/camera/blur?active=0') }}"><i class="fa fa-stop"></i>&nbsp;&nbsp;{{ trans('app.disable') }}</a>
                <a class="label {{ isActiveLabel('active',2) }}" href="{{ asset('admin/app/camera/blur?active=2') }}"><i class="fa fa-hourglass-start"></i>&nbsp;&nbsp;{{ trans('app.test') }}</a>
                @foreach($classes as $class)
                    <a class="label {{ isActiveLabel('id',$class->id) }}" href="{{ asset('admin/app/camera/blur?id='.$class->id) }}">{{ $class->name_zh }}</a>
                @endforeach
            </h4>
            <div class="row">
                @foreach($blurs as $blur)
                    <div class="col-md-3 col-xs-6 animated fadeIn push-30-t">
                        <div class="img-container fx-img-zoom-in fx-opt-zoom-in border">
                            <img class="img-responsive" src="{{ downloadUrl('blur/'. $blur->id .$blur->background) }}" alt="">
                            <div class="img-options">
                                <div class="img-options-content">
                                    <h3 class="font-w400 text-white push-5">{{ $blur->name_zh }}</h3>
                                    <h4 class="h6 font-w400 text-white-op push-15">{{ $blur->belongsToBlurClass->name_zh }}</h4>
                                    <a class="btn btn-sm btn-default" href="{{ asset('/admin/app/camera/blur/' . $blur->id)}}"><i class="fa fa-eye"></i> {{ trans('common.detail_info') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                {{ $blur->name_zh }}
                            </div>
                            <div class="col-xs-12">
                                <span>
                                    {{ $blur->belongsToBlurClass->name_zh }}
                                </span>
                            </div>
                            <div class="col-xs-12">
                                <span class="pull-right blur-updated-at">{{strtotime($blur->updated_at)}}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        jQuery(function(){
            jQuery('.blur-updated-at').each(function(){
                var date = Goobird.unixToDate($(this).text());
                $(this).text(date);
            });
        });
    </script>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
@endsection