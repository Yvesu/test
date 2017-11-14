@extends('admin/layer')

@section('layer-content')
    <center style="font-size: 16px;line-height: 100px; rgb(197, 213, 43);border-bottom: 3px solid #c5d52b;box-shadow:0 2px 2px rgba(0, 0, 0, 0.35);" >

        @if(session('admin.uid'))
            所属栏目: {{session('cate.name')}} <br /><br />
        @endif

        @if(isset($roleName))
            <b>权限限制 请联系管理员</b> <br />
            权限名称: {{$roleName}}<br>
            权限路由: {{$roleRoute}}<br>
        @endif
    </center>

@endsection