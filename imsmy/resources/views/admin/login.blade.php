<!--
   ______            __    _          __
  / ____/___  ____  / /_  (_)________/ /
 / / __/ __ \/ __ \/ __ \/ / ___/ __  /
/ /_/ / /_/ / /_/ / /_/ / / /  / /_/ /
\____/\____/\____/_.___/_/_/   \__,_/
-->
<!DOCTYPE html>
<!--[if IE 9]>         <html class="ie9 no-focus"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-focus"> <!--<![endif]-->
<head>
    <meta charset="utf-8">

    <title>谷鸟管理</title>

    <meta name="description" content="OneUI - Admin Dashboard Template & UI Framework created by pixelcave and published on Themeforest">
    <meta name="author" content="pixelcave">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

    <link href="{{ asset('images/logo_gk.png') }}" rel="shortcut icon" type="image/x-icon">
    <!-- Stylesheets -->
    <!-- Web fonts -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
    <link href="https://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- OneUI CSS framework -->
    <link rel="stylesheet" id="css-main" href="{{ asset('/css/admin/oneui.1.4.css') }}">
    @if(array_key_exists('skin',$_COOKIE) && $_COOKIE['skin'] !== null)
        <link rel="stylesheet" id="css-theme" href="{{ $_COOKIE['skin'] }}">
    @elseif(!array_key_exists('skin',$_COOKIE) || $_COOKIE['skin'] !== 'default')
        <link rel="stylesheet" id="css-theme" href="{{ asset('css/admin/themes/city.min.css')}}">
    @endif

    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
    <!-- <link rel="stylesheet" id="css-theme" href="assets/css/themes/flat.min.css"> -->
    <!-- END Stylesheets -->
    <style>
        #particles {
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: absolute;
            top: 0;
            z-index: -9;
        }
    </style>
</head>
<body>
<!-- Login Content -->
<div class="content overflow-hidden center-vertical">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4" style="opacity: 0.9">
            <!-- Login Block -->
            <div class="block block-themed animated fadeIn">
                <div class="block-header bg-primary">
                    <h3 class="block-title">{{ trans('common.login') }}</h3>
                </div>
                <div class="block-content block-content-full block-content-narrow">
                    <!-- Login Title -->
                    <h1 class="h2 font-w600 push-30-t push-5"><img src="{{ asset('images/logo_g.png') }}" class="btn-primary" style="border-radius:5px;width: 30px;vertical-align: top;margin-right: 2px">oobird Admin</h1>
                    <p>{{ trans('common.admin_login') }}</p>
                    <!-- END Login Title -->

                    <!-- Login Form -->
                    <!-- jQuery Validation (.js-validation-login class is initialized in js/pages/base_pages_login.js) -->
                    <!-- For more examples you can check out https://github.com/jzaefferer/jquery-validation -->
                    <form class="js-validation-login form-horizontal push-30-t" action="{{ asset('admin/signin') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                        @if(Session::has('user_login_failed'))
                            <div class="has-error">
                                <p class="help-block text-left animated fadeInDown" >{{Session::get('user_login_failed')}}</p>
                            </div>
                        @endif
                        <div class="form-group">
                            {{--<div class="col-xs-12">
                                <div class="form-material form-material-primary ">
                                    <input class="form-control" type="text" id="email" name="email" placeholder="{{ trans('common.email') }}">
                                    <label for="email">Email</label>
                                </div>
                            </div>--}}
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary  input-group">
                                    <input class="form-control" type="text" id="email" name="email" placeholder="{{ trans('common.email') }}">
                                    <label for="email">Email</label>
                                    <span class="input-group-addon">@goobird.com</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary ">
                                    <input class="form-control" type="password" id="password" name="password" placeholder="{{ trans('common.password') }}">
                                    <label for="password">Password</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="css-input switch switch-sm switch-primary">
                                    <input type="checkbox" id="remember" checked="checked" name="remember"><span></span> {{ trans('common.remember_me') }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <button class="btn btn-block btn-primary" type="submit"><i class="fa fa-sign-in pull-right"></i> {{ trans('common.login') }}</button>
                            </div>
                        </div>
                    </form>

                    <!-- END Login Form -->
                </div>
            </div>
            <!-- END Login Block -->
        </div>
    </div>
</div>
<!-- END Login Content -->

<!-- Login Footer -->
<div class="push-10-t text-center animated fadeInUp">
    <small class="text-muted font-w600"><span class="js-year-copy"></span> &copy; Goobird</small>
</div>
<!-- END Login Footer -->
<div id="particles"></div>
<!-- OneUI Core JS: jQuery, Bootstrap, slimScroll, scrollLock, Appear, CountTo, Placeholder, Cookie and App.js -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="https://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="{{ asset('/js/core/jquery.scrollLock.min.js') }}"></script>
<script src="{{ asset('/js/core/jquery.placeholder.min.js') }}"></script>
<script src="https://cdn.bootcss.com/particles.js/2.0.0/particles.min.js"></script>
<script src="{{ asset('/js/admin/app.js') }}"></script>

<!-- Page JS Plugins -->
<script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>

<!-- Page JS Code -->
<script src="{{ asset('js/core/base_pages_login.js') }}"></script>
<script>
    particlesJS('particles',{"particles":{"number":{"value":30,"density":{"enable":true,"value_area":800}},"color":{"value":"#E6E6E6"},"shape":{"type":"circle","stroke":{"width":0,"color":"#000000"},"polygon":{"nb_sides":5},"image":{"src":"img/github.svg","width":100,"height":100}},"opacity":{"value":0.8,"random":false,"anim":{"enable":false,"speed":1,"opacity_min":0.1,"sync":false}},"size":{"value":12,"random":true,"anim":{"enable":false,"speed":40,"size_min":0.1,"sync":false}},"line_linked":{"enable":true,"distance":150,"color":"#E6E6E6","opacity":0.8,"width":1},"move":{"enable":true,"speed":2,"direction":"none","random":false,"straight":false,"out_mode":"out","attract":{"enable":false,"rotateX":600,"rotateY":1200}}},"interactivity":{"detect_on":"canvas","events":{"onhover":{"enable":true,"mode":"repulse"},"onclick":{"enable":true,"mode":"push"},"resize":true},"modes":{"grab":{"distance":400,"line_linked":{"opacity":1}},"bubble":{"distance":400,"size":40,"duration":2,"opacity":8,"speed":3},"repulse":{"distance":200},"push":{"particles_nb":4},"remove":{"particles_nb":2}}},"retina_detect":true,"config_demo":{"hide_card":false,"background_color":"#b61924","background_image":"","background_position":"50% 50%","background_repeat":"no-repeat","background_size":"cover"}});
</script>

</body>
</html>