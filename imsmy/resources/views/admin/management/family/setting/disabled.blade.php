<ul class="nav nav-tabs nav-tabs-alt" id="department-nav">
    <li>
        <a href="?type=1">{{ trans('management.operating') . '(' . $operating_count .')' }}</a>
    </li>
    <li>
        <a href="?type=2">{{ trans('management.review') . '(' . $review_count .')'  }}</a>
    </li>
    <li class="active">
        <a href="?type=0">{{ trans('management.disabled') . '(' . $disabled_count .')'  }}</a>
    </li>
</ul>
<div class="block-content tab-content">
    <div class="tab-pane active">
        <table class="table table-bordered  js-table-sections">
            <thead>
            <tr>
                <th style="width:30px;padding: 0;margin: 0;"></th>
                <th class="text-center col-md-1 col-xs-2">#</th>
                <th class="hidden-xs col-md-4 col-xs-3">{{ trans('management.deptName') }}</th>
                <th class="col-md-4 col-xs-5">{{ trans('management.deptDescription') }}</th>
                <th class="text-center col-md-2 col-xs-3">{{ trans('management.actions') }}</th>
            </tr>
            </thead>
            <!-- 成员列表 使用了laravel Eager Loading -->
            @foreach($departments as $department)
                @if(null != $deptID && $department->id == $deptID)
                    <tbody class="js-table-sections-header open">
                @else
                    <tbody class="js-table-sections-header">
                    @endif
                <tr>
                    <td class="text-center">
                        <i class="fa fa-angle-right"></i>
                    </td>
                    <td class="text-center">{{ $department->id }}</td>
                    <td class="font-w600 hidden-xs">{{ $department->name }}</td>
                    <td>{{ $department->description }}<small>({{ $department->hasManyPosition->count()  }}个职位)</small></td>
                    <td class="text-center">
                        @if(0 == $department->active)
                        <div class="btn-group">
                            <button class="btn btn-xs btn-default" type="button" data-target="#department-{{ $department->id }}" data-toggle="modal"><i class="fa fa-pencil"></i></button>
                        </div>
                        <!-- 模拟态 弹出框开始 -->
                        <div class="modal fade" id="department-{{ $department->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog modal-dialog-popin">
                                <div class="modal-content">
                                    <div class="block block-themed block-transparent remove-margin-b">
                                        <div class="block-header bg-primary-dark">
                                            <ul class="block-options">
                                                <li>
                                                    <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                </li>
                                            </ul>
                                            <h3 class="block-title">{{trans('management.enable')}}-{{ trans('management.department') }}</h3>
                                        </div>
                                        <div class="block-content">
                                            <h3 class="text-center" style="margin-bottom: 20px;"> {{ trans('message.enableMsg') }}</h3>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <form  action="{{ asset('/admin/management/department/enable/' . $department->id) }}" method="POST">
                                            <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                            <button class="btn btn-sm btn-default" type="button" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                                            <button class="btn btn-sm btn-primary" type="submit" ><i class="fa fa-check"></i> {{ trans('common.sure') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 模拟态 弹出框结束 -->
                        @endif
                    </td>
                </tr>
                </tbody>
                <tbody>
                @foreach($department->hasManyPosition as $item)
                    @if(0 == $item->active)
                    <tr class="bg-yellow-lighter">
                        <td class="text-right" style="border-right: 0"></td>
                        <td class="font-w600 text-right" style="border-width: 1px 0;"></td>
                        <td class="hidden-xs text-primary" style="border-width: 1px 0;">{{ $item->name }}</td>
                        <td class="text-primary" style="border-width: 1px 0;"><small>{{ $item->description }}({{ $item->hasManyAdmin->count()  }}人)</small></td>
                        <td class="text-center" style="border-left: 0;">
                            @if(0 != $department->active)
                            <div class="btn-group">
                                <button class="btn btn-primary btn-xs btn-default" type="button" data-target="#post-{{ $item->id }}" data-toggle="modal"><i class="fa fa-pencil"></i></button>
                            </div>
                            <!-- 模拟态 弹出框开始 -->
                            <div class="modal fade" id="post-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                                <div class="modal-dialog modal-dialog-popin">
                                    <div class="modal-content">
                                        <div class="block block-themed block-transparent remove-margin-b">
                                            <div class="block-header bg-primary-dark">
                                                <ul class="block-options">
                                                    <li>
                                                        <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                    </li>
                                                </ul>
                                                <h3 class="block-title">{{trans('management.enable')}}-{{ trans('management.position') }}</h3>
                                            </div>
                                            <div class="block-content">
                                                <h3 class="text-center" style="margin-bottom: 20px;"> {{ trans('message.enableMsg') }}</h3>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <form  action="{{ asset('/admin/management/position/enable/' . $item->id) }}" method="POST">
                                                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                                <button class="btn btn-sm btn-default" type="button" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                                                <button class="btn btn-sm btn-primary" type="submit" ><i class="fa fa-check"></i> {{ trans('common.sure') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 模拟态 弹出框结束 -->
                            @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            @endforeach
        </table>
        {!! (new \App\Services\Presenter($departments->appends(['type' => 0])))->render() !!}
    </div>
</div>