@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/label') }}">{{trans('common.label')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.label')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/label/create') }}">
                    {{ trans('content.add_label') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block-content">
        <h4 class="blur-nav">
            <a class="label {{ isActiveLabel('active',1) }}" href="{{ asset('admin/content/label?active=1') }}"><i class="fa fa-play"></i>&nbsp;&nbsp;{{ trans('content.normal') }}</a>
            <a class="label {{ isActiveLabel('active',0) }}" href="{{ asset('admin/content/label?active=0') }}"><i class="fa fa-stop"></i>&nbsp;&nbsp;{{ trans('content.disable') }}</a>
        </h4>
        @foreach($labels->chunk(4) as $chunk)
            <div class="row push-30-t">
                @foreach($chunk as $label)
                    <div class="col-sm-6 col-lg-3">
                        <a class="block block-rounded block-link-hover3" href="{{ asset('admin/content/label/'. $label->id) }}">
                            <div class="block-content block-content-full clearfix">
                                <div class="text-center push-10-t">
                                    <div class="font-w600 push-5 label-name">{{ $label->name }}</div>
                                    <div class="text-muted created-at">{{strtotime($label->created_at)}}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        jQuery(function(){
            jQuery('.created-at').each(function(){
                var date = Goobird.unixToDate($(this).text());
                $(this).text(date);
            });
        });
    </script>
@endsection

@section('css')
    @parent
    <style>
        .label-name {
            -ms-word-break: break-all;
            word-break: break-all;
            -webkit-hyphens: auto;
            -moz-hyphens: auto;
            hyphens: auto;
        }
    </style>
@endsection
