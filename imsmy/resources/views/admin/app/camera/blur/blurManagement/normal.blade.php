<ul class="nav nav-tabs nav-tabs-alt" id="department-nav">
    <li class="active" >
        <a href="?type=1">{{ trans('app.normal')  }}</a>
    </li>
    <li>
        <a href="?type=0">{{ trans('app.disable')}}</a>
    </li>
</ul>
<div class="block-content tab-content">
    <div class="tab-pane active">
        <table class="table table-bordered  js-table-sections">
            <thead>
            <tr>
                <th class="text-center col-md-1 col-xs-2">#</th>
                <th class="col-md-4 col-xs-3">{{ trans('app.blur_class_name') }} - <small>{{ trans('multi-lang.zh') }}</small></th>
                <th class="hidden-xs col-md-4 col-xs-5">{{ trans('app.blur_class_name') }} - <small>{{ trans('multi-lang.en') }}</small></th>
                <th class="text-center col-md-2 col-xs-3">{{ trans('management.actions') }}</th>
            </tr>
            </thead>
            <tbody class="js-table-sections-header">
                @foreach($classes as $class)

                <tr>
                    <td class="text-center">{{ $class->id }}</td>
                    <td class="font-w600">{{ $class->name_zh }}</td>
                    <td class="hidden-xs">{{ $class->name_en }}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-xs btn-default" type="button" data-target="#class-{{ $class->id }}" data-toggle="modal"><i class="fa fa-pencil"></i></button>
                        </div>
                        <!-- 模拟态 弹出框开始 -->
                        <div class="modal fade" id="class-{{ $class->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog modal-dialog-popin">
                                <div class="modal-content">
                                    <div class="block block-themed block-transparent remove-margin-b">
                                        <div class="block-header bg-primary-dark">
                                            <ul class="block-options">
                                                <li>
                                                    <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                </li>
                                            </ul>
                                            <h3 class="block-title">{{trans('common.detail_info')}}-{{ trans('app.blur_class') }}</h3>
                                        </div>
                                        <div class="block-content">
                                            <div class="row">
                                                <div class="col-sm-6" style="margin-top: 30px;">
                                                    <div class="form-material">
                                                        <input class="form-control" type="text" id="material-disabled"  disabled="" value="{{ $class->name_zh }}">
                                                        <label for="material-disabled">{{ trans('app.blur_class_name') }} - <small>{{ trans('multi-lang.zh') }}</small></label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6" style="margin-top: 30px;">
                                                    <div class="form-material">
                                                        <input class="form-control" type="text" id="material-disabled"  disabled="" value="{{ $class->name_en }}">
                                                        <label for="material-disabled">{{ trans('app.blur_class_name') }} - <small>{{ trans('multi-lang.en') }}</small></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12" style="margin-top: 30px;">
                                                    <div class="form-material text-left">
                                                        <span><strong>{{ trans('common.icon') }} :</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" style="margin-bottom: 30px">
                                                <div class="col-md-6" style="margin-top: 30px;">
                                                    <div class="form-material">
                                                        @if(is_null($class->icon_sm))
                                                            <img class="img-responsive img-thumbnail center-block" id="icon-sm" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:200px;">
                                                        @else
                                                        <img class="img-thumbnail"
                                                             src="{{
                                                                privateDownloadUrl('blur_class/'. $class->id . '/' . $class->icon_sm)
                                                             }}">
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6" style="margin-top: 30px;">
                                                    <div class="form-material">
                                                        @if(is_null($class->icon_lg))
                                                            <img class="img-responsive img-thumbnail center-block" id="icon-lg" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:200px;">
                                                        @else
                                                        <img class="img-thumbnail"
                                                             src="{{
                                                                privateDownloadUrl('blur_class/'. $class->id . '/' . $class->icon_lg)
                                                             }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ asset('/admin/app/camera/blur/class/disabled/'. $class->id) }}" method="POST">
                                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                            <button class="btn btn-sm bg-red" type="submit" ><i class="fa fa-ban"></i> {{ trans('app.disable') }}</button>
                                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/app/camera/blur/class/edit/'.$class->id) }}"><i class="fa fa-pencil-square-o"></i> {{ trans('common.edit') }}</a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 模拟态 弹出框结束 -->
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>
        {!! (new \App\Services\Presenter($classes->appends(['type' => 1])))->render() !!}
    </div>
</div>