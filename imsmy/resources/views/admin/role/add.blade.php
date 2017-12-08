@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.reply') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/role/index') }}">{{trans('common.role_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_role')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/role/index') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="block-content tab-content">
            <div class="col-md-10 col-xs-12 block">
                @if (count($errors) > 0)
                    <div class="mws-form-message error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="js-validation-topic form-horizontal"
                      enctype="multipart/form-data"
                      action="{{ asset('/admin/role/insert') }}"
                      method="POST">
                    <input type="hidden" name="_timezone" value="Asia/Shanghai">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">

                    {{--组名称--}}
                    <div class="form-group">
                        <div class="col-md-8">
                            <label for="exampleInputEmail1">组名称 <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="组名称">
                            </div>
                        </div>
                    </div>

                    {{--组描述--}}
                    <div class="form-group">
                        <div class="col-md-8">
                            <label for="exampleInputEmail1">组描述 <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="intro" name="intro" value="{{ old('intro') }}" placeholder="组描述">
                            </div>
                        </div>
                    </div>

                    {{--状态设置--}}
                    <div class="form-group">
                        <div class="col-md-12">
                            <div>
                                <label for="exampleInputEmail1">权限菜单 <span class="text-danger">*</span></label>
                            </div>
                            <div class="form-material form-material-primary col-md-10 col-md-offset-2">
                                @foreach($datas as $v)

                                    <div style="{{ $v->pid == 0 ? 'margin:20px 0;padding:3px 8px;border:1px solid #ddd' : 'margin-left:20px;' }}">
                                        <input name ="menu[]" type="checkbox" value="{{ $v->id }}" /> {{ $v->name }}
                                    </div>

                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;margin-bottom: 5%">
                        <div class="col-md-6 col-md-offset-3 col-xs-12 ">
                            <button type="submit" class="btn btn-block btn-primary" id="submit">{{ trans('management.commit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
