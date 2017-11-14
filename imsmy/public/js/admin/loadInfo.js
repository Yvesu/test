/**
 * 视频的屏蔽与通过选择
 * @param obj
 * @private
 */
function _applyActive(obj){
    var id = obj.attr('data');
    $.get('/admin/content/ajax/apply',{
        id:id,
        active:obj.attr('status'),
    },function(data){
        if (data==1){
            layer.msg('操作成功');
            $('.videoCheck>a[data='+id+']').parents('div.divStatus').remove();
        }else{
            layer.msg('操作失败');
        }
    },'json');
}

/**
 * 审批视频频道选择 通过AJAX实现，目前尚未实现，只用表单提交方式提交数据预处理数据
 * @param obj
 * @private
 */
//function _chooseActive(obj){
//    var id = obj.attr('data');
//    $.get('/admin/content/ajax/choose',{
//        id:id,
//        active:1,
//        channel_id:
//    },function(data){
//        if (data==1){
//            layer.msg('操作成功');
//            $('.videoCheck>a[data='+id+']').parents('div.divStatus').remove();
//        }else{
//            layer.msg('操作失败');
//        }
//    },'json');
//}

//info 配置  id  页数
var loadInfoConf = {};
loadInfoConf.id = {2:'#recommend',3:'#movie',4:'#star',5:'#tourism',6:'#delicacy',7:'#music',8:'#dance'};
loadInfoConf.page = {2:1,3:1,4:1,5:1,6:1,7:1,8:1};

/**
 * ajax 请求频道信息 已通过视频
 * @param  type     频道类型
 */
function loadInfo(type){
    if(!type) return ;

    $.get('/admin/content/ajax/video',{
        page:loadInfoConf.page[type],
        type:type,
        active:1
    },function(data) {
        if(loadInfoConf.page[type] >= data.last_page) {
            $(loadInfoConf.id[type] + ' .more').remove();
        }

        //下一页数
        loadInfoConf.page[type] = data.current_page+1;

        $.each(data.data,function(k,item){
            $('<div class="col-sm-6 col-md-4 col-lg-2 divStatus" style="margin-bottom:10px"> <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls preload="none"  poster="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.screen_shot+'" data-setup="{}" webkit-playsinline> <source src="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.video+'" type="video/mp4"> </video> <div class="col-xs-12" style="margin-top: 5px;"> <div><p class="bg-info">内容：'+item.content+'</p></div> <div class="col-sm-4 videoCheck"> <a class="btn btn-danger" type="button" data="'+item.id+'" status="2" onclick="_applyActive($(this))">屏蔽</a> </div> <div class="col-sm-4"> <a href="#" class="btn  btn-default" role="button" disabled="disabled">已通过</a> </div> <div class="col-sm-4"> <a class="btn btn-success" type="button" href="/admin/content/video/'+item.id+'/edit">编辑</a> </div> </div></div>').appendTo(loadInfoConf.id[type] + ' .content');

        });
    },'json');
}

/**
 * 点击通过按钮，弹框，并将视频id存入提交键中
 * @param obj
 * @private
 */
function _loadSuccess(obj){
    var id = obj.attr('data');
    $('#submit-button').attr('value',id);

}

/**
 * ajax 请求频道信息 待审批视频
 * @param  type     频道类型
 */
function loadCheck(type){
    //if(!type) return ;
    $.get('/admin/content/ajax/video',{
        page:loadInfoConf.page[0],
        type:0,
        active:0
    },function(data) {
        if(loadInfoConf.page[0] >= data.last_page) {
            $('#notdefined .more').remove();
        }

        //下一页数
        loadInfoConf.page[0] = data.current_page+1;

        $.each(data.data,function(k,item){
            $('<div class="col-sm-6 col-md-4 col-lg-2 divStatus" style="margin-bottom:10px">' +
                ' <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls preload="none"  poster="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.screen_shot+'" data-setup="{}" webkit-playsinline>' +
                ' <source src="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.video+'" type="video/mp4">' +
                ' </video>' +
                ' <div class="col-xs-12" style="margin-top: 5px;"> <div><p class="bg-info">内容：'+item.content+'</p></div>' +
                ' <div class="col-sm-4 col-md-offset-2 videoCheck">' +
                ' <a class="btn btn-danger" type="button" data="'+item.id+'" status="2" onclick="_applyActive($(this))">屏蔽</a> </div> ' +
                '<div class="col-sm-6"> <a class="btn  btn-success" type="button" data="'+item.id+'" onclick="_loadSuccess($(this))" data-toggle="modal" data-target="#myModal">通过</a> ' +
                '</div> </div></div>').appendTo('#notdefined .content');
        });
    },'json');
}

//function loadCheck(type){
//    //if(!type) return ;=
//    var page = 1;
//    $.get('/admin/content/ajax/video',{
//        page:1,
//        type:0,
//        active:0
//    },function(data) {
//        if(page >= data.last_page) {
//            $('#notdefined .more').remove();
//        }
//
//        //下一页数
//        page = data.current_page+1;
//
//        $.each(data.data,function(k,item){
//            $('<div class="col-sm-6 col-md-4 col-lg-3 divStatus" style="margin-bottom:10px">' +
//                ' <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls preload="none"  poster="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.screen_shot+'" data-setup="{}" webkit-playsinline>' +
//                ' <source src="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.video+'" type="video/mp4">' +
//                ' </video>' +
//                ' <div class="col-xs-12" style="margin-top: 5px;"> <div><p class="bg-info">内容：'+item.content+'</p></div>' +
//                ' <div class="col-sm-4 col-md-offset-2 videoCheck">' +
//                ' <a class="btn btn-danger" type="button" data="'+item.id+'" status="2" onclick="_applyActive($(this))">屏蔽</a> </div> ' +
//                '<div class="col-sm-6"> <a class="btn  btn-success" type="button" data="'+item.id+'" status="1" onclick="_applyActive($(this))" data-toggle="modal" data-target="#myModal">通过</a> ' +
//                '</div> </div></div>').appendTo('#notdefined .content');
//        });
//    },'json');
//}

/**
 * ajax 请求频道信息 已屏蔽视频
 * @param  type     频道类型
 */
//function loadForbid(type){
//    if(!type) return ;
//
//    $.get('/admin/content/ajax/video',{
//        page:loadInfoConf.page[type],
//        type:type,
//        active:2
//    },function(data) {
//        if(loadInfoConf.page[type] >= data.last_page) {
//            $(loadInfoConf.id[type] + ' .more').remove();
//        }
//
//        //下一页数
//        loadInfoConf.page[type] = data.current_page+1;
//
//        $.each(data.data,function(k,item){
//            $('<div class="col-sm-6 col-md-4 col-lg-3 divStatus" style="margin-bottom:10px"> <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls preload="none"  poster="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.screen_shot+'" data-setup="{}" webkit-playsinline> <source src="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.video+'" type="video/mp4"> </video> <div class="col-xs-12" style="margin-top: 5px;"> <div><p class="bg-info">内容：'+item.content+'</p></div>  <div class="col-sm-4 col-md-offset-2"> <a href="#" class="btn  btn-default" role="button" disabled="disabled">已屏蔽</a> </div><div class="col-sm-6 videoCheck"> <a class="btn btn-success" type="button" data="'+item.id+'" status="1" onclick="_applyActive($(this))">通过</a> </div> </div></div>').appendTo(loadInfoConf.id[type] + ' .content');
//        });
//    },'json');
//}

/**
 * ajax 请求频道信息 已屏蔽视频
 */
function loadForbid(type){
    //if(!type) return ;
    $.get('/admin/content/ajax/video',{
        page:loadInfoConf.page[0],
        type:0,
        active:2
    },function(data) {
        if(loadInfoConf.page[0] >= data.last_page) {
            $('#forbid .more').remove();
        }

        //下一页数
        loadInfoConf.page[0] = data.current_page+1;

        $.each(data.data,function(k,item){
            $('<div class="col-sm-6 col-md-4 col-lg-2 divStatus" style="margin-bottom:10px">' +
                ' <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls preload="none"  poster="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.screen_shot+'" data-setup="{}" webkit-playsinline>' +
                ' <source src="http://7xtg0b.com1.z0.glb.clouddn.com/'+item.video+'" type="video/mp4">' +
                ' </video>' +
                ' <div class="col-xs-12" style="margin-top: 5px;"> <div><p class="bg-info">内容：'+item.content+'</p></div>' +
                ' <div class="col-sm-4 col-md-offset-2 videoCheck">' +
                '<div class="col-sm-6"> <a class="btn  btn-success" type="button" data="'+item.id+'" onclick="_loadSuccess($(this))" data-toggle="modal" data-target="#myModal">通过</a> ' +
                '</div> </div></div>').appendTo('#forbid .content');
        });
    },'json');
}
