@extends('/layouts/template')
@section('title','谷鸟')

@section('style')

    <link href="/test/css/video-js.min.css" rel="stylesheet">
    <script src="/test/js/video.min.js"></script>

@endsection

    @section('body')
    {{--视频测试--}}
    <div style="background-color: black;">
        <div class="container col-lg-12 bootstrap-fifty">
            {{--<div class="col-lg-10 col-md-10 col-md-offset-1">--}}
                {{--<!-- 16:9 aspect ratio -->--}}
                {{--<div class="embed-responsive embed-responsive-16by9">--}}
                    {{--<iframe class="embed-responsive-item" src="http://7xtg0b.com1.z0.glb.clouddn.com/tweet/82/o_1b1r981ka69ceou2n2j31c4n9output.mp4"></iframe>--}}
                {{--</div>--}}

                {{--<!-- 4:3 aspect ratio -->--}}
                {{--<div class="embed-responsive embed-responsive-4by3">--}}
                {{--<iframe class="embed-responsive-item" src="http://le.video.xinpianchang.com/5795e1182e907.mp4"></iframe>--}}
                {{--</div>--}}
            {{--</div>--}}
            <div class="col-lg-10 col-md-10 col-md-offset-1">
                <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="100%" height="56.25%" poster="http://7xtg0b.com1.z0.glb.clouddn.com/tweet/195/14822056753346176_640*360_.jpg" data-setup="{}">
                    <source src="http://7xtg0b.com1.z0.glb.clouddn.com/tweet/195/o_1b4d5dkcn1uv91qg7ngm14hq4rmb13.mp4" type='video/mp4'/>
                    {{--<track kind="captions" src="demo.captions.vtt" srclang="en" label="English"></track>--}}
                    <!-- Tracks need an ending tag thanks to IE9 -->
                    {{--<track kind="subtitles" src="demo.captions.vtt" srclang="en" label="English"></track>--}}
                    <!-- Tracks need an ending tag thanks to IE9 -->
            </div>
        </div>
    </div>

    <div class="container col-lg-12 bootstrap-fifty mt20" style="border:1px solid red;height:600px;">
        <div class="col-md-8 pull-left" style="border:1px solid green;background-color: #fff;height:200px;"></div>
        <div class="col-md-4 pull-right" style="border:1px solid yellow;background-color: #fff;height:200px;"></div>
    </div>




@endsection