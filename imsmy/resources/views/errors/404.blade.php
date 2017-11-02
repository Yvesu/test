@extends('index')

@section('title',404)

@section('css')
        <!-- OneUI CSS framework -->
<link rel="stylesheet" id="css-main" href="{{ asset('/css/admin/oneui.css') }}">
@endsection

@section('content')
        <!-- Error Content -->
<div class="content bg-white text-center pulldown overflow-hidden">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <!-- Error Titles -->
            <h1 class="font-s128 font-w300 text-city animated flipInX">404</h1>
            <h2 class="h3 font-w300 push-50 animated fadeInUp">
                {{ trans('errors.404') }}
            </h2>
            <!-- END Error Titles -->
        </div>
    </div>
</div>
<!-- END Error Content -->

<!-- Error Footer -->

@endsection