@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.information') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/information/index') }}">{{ trans('common.information').trans('common.management') }}</a></li>
                <li>{{ trans($press->id) }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.details')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/information/edit?id='.$press->id) }}">
                    {{ trans('common.edit') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        所属栏目{{ ' : &nbsp;&nbsp;&nbsp;&nbsp;' . $nav->belongsToPid['name'].' > '.$nav->name }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('common.www').trans('common.title') . ' : &nbsp;&nbsp;&nbsp;&nbsp;' . $press->title }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('common.article').trans('common.title') . ' : &nbsp;&nbsp;&nbsp;&nbsp;' . $press->intro }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.content') . ' : ' }}
                    </div>
                    <div class="row push-30-t" style="margin-bottom: 10px;">
                        <div class="col-md-11 col-md-offset-1" style="border: 1px solid #eee;">
                            {!! html_entity_decode($press -> hasOneContent['content']) !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
