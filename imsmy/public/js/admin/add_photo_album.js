/**
 * Created by Administrator on 2016/5/24.
 */
var uploader = Qiniu.uploader({
    runtimes: 'html5,flash,html4',      // 上传模式,依次退化
    browse_button: 'pickfiles',         // 上传选择的点选按钮，**必需**
    // 在初始化时，uptoken, uptoken_url, uptoken_func 三个参数中必须有一个被设置
    // 切如果提供了多个，其优先级为 uptoken > uptoken_url > uptoken_func
    // 其中 uptoken 是直接提供上传凭证，uptoken_url 是提供了获取上传凭证的地址，如果需要定制获取 uptoken 的过程则可以设置 uptoken_func
    // uptoken : '<Your upload token>', // uptoken 是上传凭证，由其他程序生成
    uptoken_url: '/admin/up_token',         // Ajax 请求 uptoken 的 Url，**强烈建议设置**（服务端提供）
    // uptoken_func: function(file){    // 在需要获取 uptoken 时，该方法会被调用
    //    // do something
    //    return uptoken;
    // },
    get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
    // downtoken_url: '/downtoken',
    // Ajax请求downToken的Url，私有空间时使用,JS-SDK 将向该地址POST文件的key和domain,服务端返回的JSON必须包含`url`字段，`url`值为该文件的下载地址
    //unique_names: true,              // 默认 false，key 为文件名。若开启该选项，JS-SDK 会为每个文件自动生成key（文件名）
    //save_key: true,                  // 默认 false。若在服务端生成 uptoken 的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
    domain: Lang.domain,     // bucket 域名，下载资源时用到，**必需**
    container: 'upload-file',             // 上传区域 DOM ID，默认是 browser_button 的父元素，
    max_file_size: '200mb',             // 最大文件体积限制
    flash_swf_url: 'Moxie.swf',  //引入 flash,相对路径
    max_retries: 3,                     // 上传失败最大重试次数
    dragdrop: true,                     // 开启可拖曳上传
    drop_element: 'upload-file',          // 拖曳上传区域元素的 ID，拖曳文件或文件夹后可触发上传
    chunk_size: '4mb',                  // 分块上传时，每块的体积
    auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传,
    //x_vars : {
    //    自定义变量，参考http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html
    //    'time' : function(up,file) {
    //        var time = (new Date()).getTime();
    // do something with 'time'
    //        return time;
    //    },
    //    'size' : function(up,file) {
    //        var size = file.size;
    // do something with 'size'
    //        return size;
    //    }
    //},
    init: {
        'FilesAdded': function(up, files) {
            plupload.each(files, function(file) {

                // 文件添加进队列后,处理相关的事情
                if($('#upload-temp').siblings().length >= 9){
                    uploadCancel(up,file);
                    return false;
                }

                var $temp = $('#upload-temp');
                var $html = $temp.html();
                var $parent = $temp.parent();
                $parent.append('<div id="' + file.id + '" class="col-md-8 col-md-offset-2">' + $html + '</div>');
                $('#'+ file.id +' .upload-cancel')
                    .bind('click',function(){
                        uploadCancel(up,file);
                    });
                console.log('FilesAdded');
            });
        },
        'BeforeUpload': function(up, file) {
            // 每个文件上传前,处理相关的事情
            if('image/jpeg' !== file.type && 'image/png' !== file.type){
                uploadCancel(up,file);
                $('#upload-file > .help-block').removeClass('hidden');
                return false;
            }
            $('#upload-file > .help-block').addClass('hidden');
            $('#submit').addClass('disabled');
        },
        'UploadProgress': function(up, file) {
            // 每个文件上传时,处理相关的事情
            var percent = (100 * (file.loaded / file.size)).toFixed(2);
            $('#' + file.id +' .upload-file-bar').width(file.percent + '%');
            $('#' + file.id +' .upload-file-text').text(Lang.uploaded + ' : ' + percent + '%  ' +
                Lang.speed + ' : ' + plupload.formatSize(file.speed).toUpperCase());

        },

        'FileUploaded': function(up, file, info) {
            // 每个文件上传成功后,处理相关的事情
            // 其中 info 是文件上传成功后，服务端返回的json，形式如
            // {
            //    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
            //    "key": "gogopher.jpg"
            //  }
            // 参考http://developer.qiniu.com/docs/v6/api/overview/up/response/simple-response.html
            var domain = up.getOption('domain');
            var res = $.parseJSON(info);

            var imageInfoObj = Qiniu.imageInfo(res.key); // 获取上传图片的宽和高还有格式等信息
            res.key = res.key +'@' + imageInfoObj.width +'*'+ imageInfoObj.height; // 将宽高拼接进文件名,只是返回，并不存在七牛

            var sourceLink = domain + res.key; //获取上传成功后的文件的Url

            console.log('Key : ' + sourceLink);
            console.log('Hash : ' + res.hash);
            $('#'+ file.id +' .upload-file-text').html(Lang.completed + '!  ' +
                Lang.file_size +':' + plupload.formatSize(file.size).toUpperCase() +
                '  <a href="' + sourceLink + '" target="_blank">   ' +
                Lang.click_view +
                '</a>' );

            var $keys = $('input#keys');
            var $value = $keys.val();
            if($value == ''){
                $value = [];
            }else{
                $value = $value.split(',');
            }
            $value.push(res.key);
            $keys.val($value.toString());
        },
        'Error': function(up, err, errTip) {
            //上传出错时,处理相关的事情
            $('#submit').removeClass('disabled');
            $('input#key').val(null);
        },
        'UploadComplete': function() {
            //队列文件处理完毕后,处理相关的事情
            $('#submit').removeClass('disabled');
        },
        'Key': function(up, file) {
            // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
            // 该配置必须要在 unique_names: false , save_key: false 时才生效

            var fileExtension = file.name.substring(file.name.lastIndexOf('.') + 1);

            //var key = 'temp/' + file.id + file.name; // 旧代码 搜索词：备忘录
            var key = 'temp/' + file.id + Date.parse(new Date()) +Math.round(Math.random()*1000000) + '.' + fileExtension; // 新,将大小属性存进去
            //console.log(key);
            // do something with key here
            return key
        }

    }
});



var uploadCancel = function(up,file){
    $('#' + file.id).remove();
    up.removeFile(file);
    var $keys = $('input#keys');
    var $value = $keys.val();
    $value = $value.split(',');
    for(var i in $value){
        if ('temp/' + file.id + file.name == $value[i]) {
            $value.splice(i,1);
        }
    }
    $keys.val($value.toString());
};