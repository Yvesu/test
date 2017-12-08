@extends('admin.layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                {{--TODO i don't know what to write--}}
                {{--<ol class="breadcrumb push-10-t">
                    <li>{{ trans('common.management') }}</li>
                    <li><a class="link-effect" href="">{{trans('common.family_management')}}</a></li>
                </ol>--}}
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{ trans('common.detail_info') }}
                </h1>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <div class="block" style="padding: 30px 0">
            <div class="row">
                <div class="col-md-3 col-md-offset-1 text-center">
                    {{--<img src="{{ asset('/admin/images/'.$administrator->avatar) }}" style="max-height:128px;max-width:128px">--}}
                    <img id="user-avatar" src="{{ asset('/admin/images/'.$administrator->avatar) }}" style="max-height:128px;max-width:128px">
                    <div id="validation-errors"></div>
                    <div class="avatar-upload" id="avatar-upload">
                        {!! Form::open( [ 'url' => ['/admin/account/avatar'], 'method' => 'POST', 'id' => 'upload', 'files' => true ] ) !!}
                        <a href="#" class="btn button-change-profile-picture">
                            <label for="upload-profile-picture">
                                <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('message.uploadNewAvatar') }}</span>
                                <input name="upload-image" id="upload-image" type="file" title style="margin-top:-32px;">
                            </label>
                        </a>
                        {!! Form::close() !!}
                        <div class="span5">
                            <div id="output" style="display:none">
                            </div>
                        </div>
                        <span id="filename"></span>
                    </div>
                </div>
                <div class="col-md-7  text-left margin-10">
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('common.email')}} : <ins>{{ $administrator->email }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.phone')}} : <ins>{{ $administrator->phone }}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('common.name')}} : <ins>{{ $administrator->name }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.sex')}} :
                                @if($administrator->sex)
                                    <ins>{{ trans('common.male') }}</ins>
                                @else
                                    <ins>{{ trans('common.female') }}</ins>
                                @endif

                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('management.department')}} : <ins>{{ $administrator->belongsToPosition->belongsToDepartment->description }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('management.position')}} : <ins>{{ $administrator->belongsToPosition->description }}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span>
                                {{ trans('management.permissions') }} : <ins>{{--TODO Permissions--}}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row border-t" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <h5>
                                {{ trans('management.secondary_contact') }}:
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.name')}} : <ins>{{ $administrator->secondary_contact_name }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.phone')}} : <ins>{{ $administrator->secondary_contact_phone }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.relationship')}} : <ins>{{ trans('management.'.$administrator->secondary_contact_relationship) }} </ins>
                            </span>
                        </div>
                    </div>
                    <div class="row border-t js-gallery" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <h5>
                                {{ trans('management.IDCard') }}:
                            </h5>
                        </div>
                        <div class="col-md-4" style="max-width: 200px;max-height: 200px">
                            <div class="img-container fx-opt-zoom-in">
                                <img class="img-responsive img-thumbnail" src="{{ asset('/admin/images/'.$administrator->ID_card_URL) }}" alt="" >
                                <div class="img-options">
                                    <div class="img-options-content">
                                        <a class="btn btn-sm btn-default img-link" href="{{ asset('/admin/images/'.$administrator->ID_card_URL) }}">
                                            <i class="fa fa-search-plus"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open( [ 'url' => ['/admin/account/avatar/crop'], 'method' => 'POST', 'onsubmit'=>'return checkCoords();','files' => true ] ) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color: #ffffff">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">裁剪头像</h4>
                </div>
                <div class="modal-body">
                    <div class="content">
                        <div class="crop-image-wrapper">
                            <img
                                    src="/images/default-avatar.png"
                                    class="ui centered image" id="cropbox" >
                            <input type="hidden" id="photo" name="photo" />
                            <input type="hidden" id="x" name="x" />
                            <input type="hidden" id="y" name="y" />
                            <input type="hidden" id="w" name="w" />
                            <input type="hidden" id="h" name="h" />
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">裁剪头像</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/plugins/magnific-popup/magnific-popup.min.js') }}"></script>
    <script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.min.js"></script>
    <script src="{{ asset('js/plugins/jcrop/jquery.Jcrop.min.js') }}"></script>
    <script src="{{ asset('js/admin/upload-image.js') }}"></script>
    <script>
        $(function(){
            App.initHelpers('magnific-popup');
        });

        //多语言
        Lang.uploadNewAvatar    =   "{{ trans('message.uploadNewAvatar') }}";
        Lang.uploading          =   "{{ trans('message.uploading') }}";
    </script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/magnific-popup/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/jcrop/jquery.Jcrop.css') }}">
@endsection