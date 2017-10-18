/**
 * Created by Administrator on 2016/3/12.
 */
var Goobird = function(){
    /**
     * 添加浏览器Cookie信息
     * @param name      名称
     * @param value     值
     * @param days      有效天数
     */
    var _createCookie = function(name,value,days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    };

    var languages = function(){
        $("a.multiple-lang").click(function(){
            _createCookie("Lang",this.name,9999);
            location.reload();
            return false;
        });
    };

    var unixToDate = function (unix){
        var  date = new Date(unix*1000);
        var  year   = date.getFullYear();
        var  month  = date.getMonth() + 1;
        var  day    = date.getDate();
        var  hour   = date.getHours();
        var  minute = date.getMinutes();
        var  second = date.getSeconds();
        return   year+"-"+month+"-"+day+"   "+padNum(hour,2)+":"+padNum(minute,2)+":"+padNum(second,2);
    };

    var dateToUnix = function (date){
        var tmp_datetime = date.replace(/:/g,'-');
        tmp_datetime = tmp_datetime.replace(/ /g,'-');
        var arr = tmp_datetime.split("-");
        var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3],arr[4],arr[5]));
        return now;
    };

    var padNum =  function(num, n) {
        var len = num.toString().length;
        while(len < n) {
            num = "0" + num;
            len++;
        }
        return num;
    };

    return{
        /**
         * 初始化页面
         *    ->  多语言
         */
        init:function(){
            languages();
        },

        dateToUnix:function(datetime){
            return dateToUnix(datetime);
        },

        unixToDate:function(date){
            return unixToDate(date);
        }

    };
}();


var PreviewImage = function(){
    var _preview = function(file){
        if (file.files && file.files[0])
        {
            if(!/\.(gif|jpg|png|GIF|JPG|PNG)$/.test(file.files[0].name))
            {
                return false;
            }
            var img = document.getElementById('IDCard-image');
            img.onload = function(){

            };
            var reader = new FileReader();
            reader.onload = function(evt){
                img.src = evt.target.result;
                $('#add-admin-IDCard-error').remove();
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _blur_preview = function(file,id){
        if (file.files && file.files[0])
        {
            var $format = jQuery('.format-invalid.' + id);
            var $proportion = jQuery('.proportion-invalid.' + id);
            $format.addClass('hidden');
            $proportion.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $('#blur-class-' + id).val(null);
                $format.removeClass('hidden');
                $('#' + id).attr('src','/admin/images/default/default_icon.png');
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节)
            if(/\.(gif|GIF)$/.test(file.files[0].name)){
                if(file.files[0].size>358400){
                    $size.removeClass('hidden');
                    $('#topic-' + id)
                        .val(null)
                        .attr('src',url);
                    return false;
                }
            }

            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;
                if(isValid.height != isValid.width){
                    $proportion.removeClass('hidden');
                    $('#blur-class-' + id).val(null);
                    $('#' + id).attr('src','/admin/images/default/default_icon.png');
                    return false;
                }
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _channel_preview = function(file,id,url){
        url = arguments[2] ? arguments[2] : '/admin/images/default/default_icon.png';
        if (file.files && file.files[0])
        {
            var $format = jQuery('.format-invalid.' + id);
            var $size = jQuery('.size-invalid.' + id);
            $format.addClass('hidden');
            $size.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $format.removeClass('hidden');
                $('#channel-' + id)
                    .val(null)
                    .attr('src',url);
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节) TODO 20170927 临时注释
            //if(/\.(gif|GIF)$/.test(file.files[0].name)){
            //    if(file.files[0].size>358400){
            //        $size.removeClass('hidden');
            //        $('#topic-' + id)
            //            .val(null)
            //            .attr('src',url);
            //        return false;
            //    }
            //}

            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;
                //if(isValid.height != 300 ||  isValid.width != 300){   // TODO 20170927 临时注释
                //    $size.removeClass('hidden');
                //    $('#channel-' + id)
                //        .val(null)
                //        .attr('src',url);
                //    return false;
                //}
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _topic_preview = function(file,id,url){
        url = arguments[2] ? arguments[2] : '/admin/images/default/default_icon.png';
        if (file.files && file.files[0])
        {
            console.log(file.files[0].name);
            var $format = jQuery('.format-invalid.' + id);
            var $size = jQuery('.size-invalid.' + id);
            $format.addClass('hidden');
            $size.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $format.removeClass('hidden');
                $('#topic-' + id)
                    .val(null)
                    .attr('src',url);
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节)
            if(/\.(gif|GIF)$/.test(file.files[0].name)){
                if(file.files[0].size>358400){
                    $size.removeClass('hidden');
                         $('#topic-' + id)
                             .val(null)
                             .attr('src',url);
                         return false;
                }
            }
            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;

                // 暂时关闭话题上传图片尺寸限制，需要时再打开，搜索符号：备忘录
                // if(isValid.height != 150 ||  isValid.width != 150){
                //     $size.removeClass('hidden');
                //     $('#topic-' + id)
                //         .val(null)
                //         .attr('src',url);
                //     return false;
                // }
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _video_preview = function(file,id,url){
        url = arguments[2] ? arguments[2] : '/admin/images/default/default_icon.png';
        if (file.files && file.files[0])
        {
            var $format = jQuery('.format-invalid.' + id);
            var $size = jQuery('.size-invalid.' + id);
            $format.addClass('hidden');
            $size.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $format.removeClass('hidden');
                $('#video-' + id)
                    .val(null)
                    .attr('src',url);
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节)
            if(/\.(gif|GIF)$/.test(file.files[0].name)){
                if(file.files[0].size>358400){
                    $size.removeClass('hidden');
                    $('#topic-' + id)
                        .val(null)
                        .attr('src',url);
                    return false;
                }
            }

            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;
                var scale = isValid.width / isValid.height;
                // 判断高宽比例是否合规,暂时先不做限制,搜索符号：备忘录
                //if(scale != 1 && scale != 0.5625){
                //    $size.removeClass('hidden');
                //    $('#video-' + id)
                //        .val(null)
                //        .attr('src',url);
                //    return false;
                //}
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _cinema_preview = function(file,id,url){
        url = arguments[2] ? arguments[2] : '/admin/images/default/default_icon.png';
        if (file.files && file.files[0])
        {
            var $format = jQuery('.format-invalid.' + id);
            var $size = jQuery('.size-invalid.' + id);
            $format.addClass('hidden');
            $size.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $format.removeClass('hidden');
                $('#video-' + id)
                    .val(null)
                    .attr('src',url);
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节)
            if(/\.(gif|GIF)$/.test(file.files[0].name)){
                if(file.files[0].size>358400){
                    $size.removeClass('hidden');
                    $('#topic-' + id)
                        .val(null)
                        .attr('src',url);
                    return false;
                }
            }

            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;
                var scale = isValid.width / isValid.height;
                // 判断高宽比例是否合规,暂时先不做限制,搜索符号：备忘录
                if(scale != 1){
                    $size.removeClass('hidden');
                    $('#video-' + id)
                        .val(null)
                        .attr('src',url);
                    return false;
                }
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };

    var _cinema_picture_preview = function(file,id,url){
        url = arguments[2] ? arguments[2] : '/admin/images/default/default_icon.png';
        if (file.files && file.files[0])
        {
            var $format = jQuery('.format-invalid.' + id);
            var $size = jQuery('.size-invalid.' + id);
            $format.addClass('hidden');
            $size.addClass('hidden');
            if(!/\.(gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/.test(file.files[0].name))
            {
                $format.removeClass('hidden');
                $('#video-' + id)
                    .val(null)
                    .attr('src',url);
                return false;
            }

            // 判断上传图片是否为gif格式，如果是,限制大小350k(358400字节)
            if(/\.(gif|GIF)$/.test(file.files[0].name)){
                if(file.files[0].size>358400){
                    $size.removeClass('hidden');
                    $('#topic-' + id)
                        .val(null)
                        .attr('src',url);
                    return false;
                }
            }

            var isValid = document.getElementById('is-valid');
            var img     = document.getElementById(id);
            img.onload = function(){
            };
            var reader = new FileReader();
            reader.onload = function(evt){
                isValid.src = evt.target.result;
                var scale = isValid.width / isValid.height;
                // 判断高宽比例是否合规,暂时先不做限制,搜索符号：备忘录
                //if(scale != 1 && scale != 0.5625){
                //    $size.removeClass('hidden');
                //    $('#video-' + id)
                //        .val(null)
                //        .attr('src',url);
                //    return false;
                //}
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file.files[0]);
        }
        else //兼容IE
        {
            var sFilter='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
            file.select();
            var src = document.selection.createRange().text;
            div.innerHTML = '<img id=IDCard-image>';
            var img = document.getElementById('imghead');
            img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
            var rect = clacImgZoomParam(MAXWIDTH, MAXHEIGHT, img.offsetWidth, img.offsetHeight);
            status =('rect:'+rect.top+','+rect.left+','+rect.width+','+rect.height);
            div.innerHTML = "<div id=divhead style='width:"+rect.width+"px;height:"+rect.height+"px;margin-top:"+rect.top+"px;"+sFilter+src+"\"'></div>";
        }
    };


    return {
        preview : function (file){
            _preview(file);
        },

        blur_preview : function (file,id){
            _blur_preview(file,id);
        },

        channel_preview : function (file,id,url) {
            _channel_preview(file,id,url);
        },

        topic_preview : function (file,id,url) {
            _topic_preview(file,id,url);
        },

        video_preview : function (file,id,url) {
            _video_preview(file,id,url);
        },

        cinema_preview : function (file,id,url) {
            _cinema_preview(file,id,url);
        },

        cinema_picture_preview : function (file,id,url) {
            _cinema_picture_preview(file,id,url);
        },

    }
}();