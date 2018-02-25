<!--
   ______            __    _          __
  / ____/___  ____  / /_  (_)________/ /
 / / __/ __ \/ __ \/ __ \/ / ___/ __  /
/ /_/ / /_/ / /_/ / /_/ / / /  / /_/ /
\____/\____/\____/_.___/_/_/   \__,_/
-->
<!DOCTYPE html>
<html lang="zh-CN">
<!--[if IE 9]>         <html class="ie9 no-focus"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-focus"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <!-- Bootstrap -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{ asset('images/logo_gk.png') }}" rel="shortcut icon" type="image/x-icon">
    <script src="https://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    @yield('css')
    @yield('css-framework')
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- vue.js -->
    <script src="https://cdn.bootcss.com/vue/1.0.16/vue.min.js"></script>
</head>
<body>
@yield('content')
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="https://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

@yield('scripts')
</body>
</html>