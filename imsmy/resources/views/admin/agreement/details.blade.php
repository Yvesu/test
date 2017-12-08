@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li><a class="link-effect" href="{{ asset('/admin/agreement/index') }}">{{trans('common.agreement')}}</a></li>
                <li>{{trans('common.agreement')}}{{ trans('common.details') }}</li>
                <li>{{ trans($data -> id) }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading">
                {{ trans('common.details') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/agreement/edit?id='.$data->id) }}">
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
                        {{ trans('common.title') . ' : ' . $data->title }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.content') . ' : ' }}
                    </div>
                    <div class="row push-30-t" style="margin-bottom: 10px;">
                        <div class="col-md-11 col-md-offset-1" style="border: 1px solid #eee;">
                            {!! html_entity_decode($data -> content) !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
