@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/topic') }}">{{trans('common.topic_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('content.topic'). ' : ' .$topic->name}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/content/topic/' . $topic->id) }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $topic->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                        <a class="btn btn-sm btn-success" type="button"
                           href="{{ asset('/admin/content/topic/' . $topic->id . '/edit') }}">
                            <i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}
                        </a>
                    @else
                        <input type="hidden" name="active" value="0">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    <a class="btn btn-sm btn-success" type="button"
                       href="{{ asset('/admin/content/topic/' . $topic->id . '/recommend_channel') }}">
                        <i class="fa fa-pencil-square-o"></i> {{trans('content.recommend') . '&' . trans('content.channel')}}
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="col-xs-12">
    <div class="block-content">

    </div>
</div>
@endsection