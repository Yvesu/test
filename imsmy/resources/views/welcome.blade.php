<!DOCTYPE html>
<html>
    <head>
        <title>Goobird</title>
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font: 24px  'Microsoft YaHei', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
                color: #8E8E8E;
                font-family: STFangsong, Fangsong;
            }

            .title {
                font-size: 96px;
            }
            h1{
                font-size: 72px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <h1>北京谷鸟科技有限公司</h1>
                <!-- <h3>地址：北京市朝阳区三里屯SOHO A座1609</h3> -->
                {{--<div class="title">Goobird</div>
                <a href="twitter://">Twitter</a>s
                <a href="taobao://">TaoBao</a>--}}
                {{--<a href="http://developer.qiniu.com/code/v6/api/kodo-api/image/imageview2.html">七牛文档</a>--}}
                {{--<a href="http://blog.csdn.net/crayondeng/article/details/16991579">iOS正则</a>--}}
                <div id="demo"></div>
            </div>
        </div>
        {{--<script>
            var x=document.getElementById("demo");
            function getLocation()
            {
                if (navigator.geolocation)
                {
                    navigator.geolocation.getCurrentPosition(showPosition);
                }
                else{x.innerHTML="Geolocation is not supported by this browser.";}
            }
            function showPosition(position)
            {
                x.innerHTML="Latitude: " + position.coords.latitude +
                        "<br />Longitude: " + position.coords.longitude;
            }
            getLocation();
        </script>--}}
    </body>
</html>
