@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li><a class="link-effect" href="{{ asset('/admin/maintain/index') }}">{{ trans('content.sites-maintain') }}</a></li>
                <li>{{ trans('content.on-off') }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.on-off')}}
            </h1>
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

                    {{--提交--}}
                    <div class="form-group" style="margin-top: 30px;margin-bottom: 5%">
                        <div class="col-md-6 col-md-offset-3 col-xs-12 ">
                            <button type="submit" class="btn btn-block btn-primary" onclick="return false">一键清理缓存</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('/js/layer/layer.js') }}"></script>

<script>
    $('button').click(function(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url:'/admin/maintain/cache-flush',
            type:'POST',
            success:function(data){
                if(1 == data){
                    layer.msg('操作成功');
                    location.reload();
                }else{
                    layer.msg('操作失败');
                }
            },
            error:function(data){
                layer.msg('操作失败');
            },
            timeout:3000,
            async:false
        });
    });
</script>

@endsection
