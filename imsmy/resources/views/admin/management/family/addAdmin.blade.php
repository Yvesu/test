@extends('admin.layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                <ol class="breadcrumb push-10-t">
                    <li>{{ trans('common.management') }}</li>
                    <li><a class="link-effect" href="">{{trans('common.family_management')}}</a></li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{ trans('management.add_admin') }}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn bg-red" href="/admin/management/family/member">
                        {{ trans('common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <div class="block">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Simple Classic Progress Wizard (.js-wizard-simple class is initialized in js/pages/add-admin.js) -->
                    <!-- For more examples you can check out http://vadimg.com/twitter-bootstrap-wizard-example/ -->
                    <div class="js-wizard-simple block">
                        <!-- Step Tabs -->
                        <ul class="nav nav-tabs nav-justified">
                            <li>
                                <a class="active col-xs-4 col-md-12" href="#add-admin-step1" data-toggle="tab">1. {{ trans('management.personal') }}</a>
                            </li>
                            <li class="">
                                <a class="inactive col-xs-4 col-md-12" href="#add-admin-step2" data-toggle="tab">2. {{ trans('management.details') }}</a>
                            </li>
                            <li class="">
                                <a class="inactive col-xs-4 col-md-12" href="#add-admin-step3" data-toggle="tab">3. {{ trans('management.extra') }}</a>
                            </li>
                        </ul>
                        <!-- END Step Tabs -->

                        <!-- Form -->
                        <form class="js-form form-horizontal" enctype="multipart/form-data" action="{{ asset('/admin/management/administrator') }}" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                            <!-- Steps Progress -->
                            <div class="block-content block-content-mini block-content-full border-b">
                                <div class="wizard-progress progress progress-mini remove-margin-b">
                                    <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 0"></div>
                                </div>
                            </div>
                            <!-- END Steps Progress -->

                            <!-- Steps Content -->
                            <div class="block-content tab-content">
                                <!-- Step 1 -->
                                <div class="tab-pane fade fade-up in push-30-t push-50 active" id="add-admin-step1">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <label for="add-admin-email">{{ trans('common.email') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-email" name="add-admin-email" placeholder="{{ trans('message.plzInput') }}{{ trans('common.email') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="add-admin-phone">{{ trans('common.phone') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-phone" name="add-admin-phone" placeholder="{{ trans('message.plzInput') }}{{ trans('common.phone') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <label for="add-admin-name">{{ trans('common.name') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-name" name="add-admin-name" placeholder="{{ trans('message.plzInput') }}{{ trans('common.name') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="add-admin-sex">{{ trans('common.sex') }} <span class="text-danger">*</span></label>
                                            <div style="margin: 10px 0 10px">
                                                <label class="css-input css-radio css-radio-primary push-50-r">
                                                    <input type="radio" name="add-admin-sex" value="1"><span></span> {{ trans('common.male') }}
                                                </label>
                                                <label class="css-input css-radio css-radio-primary">
                                                    <input type="radio" name="add-admin-sex" value="0"><span></span> {{ trans('common.female') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id="department-position-component">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <label for="add-admin-department">{{ trans('management.department') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary" id="add-admin-department-vue">
                                                <select class="form-control" id="add-admin-department" name="add-admin-department" v-model="department" v-on="change:select">
                                                    <option value="">{{ trans('common.plzSelect') }}</option>
                                                    @foreach($departments as $department)
                                                        <option value="{{ $department->id }}">{{ $department->description }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="add-admin-position">{{ trans('management.position') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary" id="add-admin-position-vue">
                                                <select class="form-control" id="add-admin-position" name="add-admin-position">
                                                    <option value="">{{ trans('common.plzSelect') }}</option>
                                                    <option v-for="position in positions" value="@{{ position.id }}">@{{ position.description }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Step 1 -->

                                <!-- Step 2 -->
                                <div class="tab-pane fade fade-up push-30-t push-50" id="add-admin-step2">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <label for="add-admin-secondary-contact-name">{{ trans('management.secondary_contact_name') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-secondary-contact-name" name="add-admin-secondary-contact-name" placeholder="{{ trans('message.plzInput') }}{{ trans('management.secondary_contact_name') }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="add-admin-secondary-contact-phone">{{ trans('management.secondary_contact_phone') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-secondary-contact-phone" name="add-admin-secondary-contact-phone" placeholder="{{ trans('message.plzInput') }}{{ trans('management.secondary_contact_phone') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <label for="add-admin-secondary-contact-relationship">{{ trans('management.secondary_contact_relationship') }} <span class="text-danger">*</span></label>
                                            <div class="form-material form-material-primary">
                                                {{--<input class="form-control" type="text" id="add-admin-secondary-contact-relationship" name="add-admin-secondary-contact-relationship" placeholder="{{ trans('message.plzInput') }}{{ trans('management.secondary_contact_relationship') }}">--}}
                                                <select class="form-control" id="add-admin-secondary-contact-relationship" name="add-admin-secondary-contact-relationship">
                                                    <option value="">{{ trans('common.plzSelect') }}</option>
                                                    <option value="father">{{ trans('management.father') }}</option>
                                                    <option value="mother">{{ trans('management.mother') }}</option>
                                                    <option value="brother">{{ trans('management.brother') }}</option>
                                                    <option value="sister">{{ trans('management.sister') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="add-admin-user-id">{{ trans('management.APPUserID') }}</label>
                                            <div class="form-material form-material-primary">
                                                <input class="form-control" type="text" id="add-admin-user-id" name="add-admin-user-id" placeholder="{{ trans('message.plzInput') }}{{ trans('management.APPUserID') }}">
                                            </div>
                                            <span class="animated fadeInDown has-error text-danger hidden user-id-error">{{ trans('errors.bind_user_failed') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Step 2 -->

                                <!-- Step 3 -->
                                <div class="tab-pane fade fade-up push-30-t push-50" id="add-admin-step3">
                                    <div class="form-group">
                                        <div class="col-md-4 col-md-offset-4 border remove-padding" style="min-height:200px;overflow: hidden">
                                            <img id="IDCard-image" style="width:100%;">
                                        </div>
                                        <div class="col-md-4 col-md-offset-4 col-xs-6 col-xs-offset-3 text-center" style="overflow: hidden;margin-top:10px">
                                            <span class="btn btn-success">
                                                <i class="glyphicon glyphicon-plus"></i>
                                                <span>{{ trans('message.IDCardsUpload') }}</span>
                                                <input class="upload" type="file" id="add-admin-IDCard" name="add-admin-IDCard" onchange="PreviewImage.preview(this)" >
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Step 3 -->
                            </div>
                            <!-- END Steps Content -->

                            <!-- Steps Navigation -->
                            <div class="block-content block-content-mini block-content-full border-t">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button class="wizard-prev btn btn-default" type="button"><i class="fa fa-arrow-left"></i> {{ trans('common.previous') }}</button>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <button class="wizard-next btn btn-default" type="button">{{ trans('common.next') }} <i class="fa fa-arrow-right"></i></button>
                                        <button class="wizard-finish btn btn-primary" id="testtest" type="submit"><i class="fa fa-check"></i> {{ trans('common.submit') }}</button>
                                    </div>
                                </div>
                            </div>
                            <!-- END Steps Navigation -->
                        </form>
                        <!-- END Form -->
                    </div>
                    <!-- END Simple Classic Progress Wizard -->
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    @parent
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
    <script>
        //多语言提示信息
        Lang.email_required                             = " {{ trans('message.requiredMsg',['attribute' => trans('common.email')])  }}";
        Lang.email_correct                              = " {{ trans('message.correctMsg',['attribute' => trans('common.email')]) }}"
        Lang.phone_required                             = " {{ trans('message.requiredMsg',['attribute' => trans('common.phone')])  }}";
        Lang.phone_correct                              = " {{ trans('message.correctMsg',['attribute' => trans('common.phone')])  }}";
        Lang.name_required                              = " {{ trans('message.requiredMsg',['attribute' => trans('common.name')])  }}";
        Lang.sex_required                               = " {{ trans('message.requiredMsg',['attribute' => trans('common.sex')])  }}";
        Lang.department_required                        = " {{ trans('message.requiredMsg',['attribute' => trans('management.department')])  }}";
        Lang.position_required                          = " {{ trans('message.requiredMsg',['attribute' => trans('management.position')])  }}";
        Lang.secondary_contact_name_required            = " {{ trans('message.requiredMsg',['attribute' => trans('management.secondary_contact_name')])  }}";
        Lang.secondary_contact_phone_required           = " {{ trans('message.requiredMsg',['attribute' => trans('management.secondary_contact_phone')])  }}";
        Lang.secondary_contact_phone_correct            = " {{ trans('message.correctMsg',['attribute' => trans('management.secondary_contact_phone')])  }}";
        Lang.secondary_contact_relationship_required    = " {{ trans('message.requiredMsg',['attribute' => trans('management.secondary_contact_relationship')])  }}";
        Lang.IDCard_required                            = " {{ trans('message.requiredMsg',['attribute' => trans('message.IDCardsUpload')])  }}";
        Lang.plz_select                                 = " {{ trans('message.plzSelect') }}";
        Lang.APP_user_id                                = " {{ trans('message.onlyMsg',['attribute' => trans('common.digital')]) }}";
    </script>
    <script src="{{ asset('js/admin/add-admin.js') }}"></script>
    <script src="{{ asset('js/admin/management-admin.js') }}"></script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
@endsection