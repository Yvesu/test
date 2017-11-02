/**
 * 视频审批时的屏蔽与通过选择
 * @param obj
 * @private
 */
function _videoHandle(obj){
    var id = obj.attr('data');
    var channel_id = $('#channel_id').val();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/video/apply',
        type:'POST',
        dataType:'json',
        data:{
            id:id,
            active:obj.attr('status'),
            channel_id:channel_id
        },
        success:function(data){
            if (data==1){
                layer.msg('操作成功');
                $('.videoCheck>button[data='+id+']').parents('tr.divStatus').remove();
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
}

/**
 * 视频详情页 状态操作
 * @param obj
 * @private
 */
function _videoActive(obj){
    var id = obj.attr('data');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/video/apply',
        type:'POST',
        dataType:'json',
        data:{
            id:id,
            active:obj.attr('status')
        },
        success:function(data){
            if (data==1){
                layer.msg('操作成功');
                $('.video-edit>button[data='+id+']').remove();
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
}

/**
 * 网站配置信息 的屏蔽与通过选择
 * @param obj
 * @private
 */
function _sitesConfigHandle(obj){
    var id = obj.attr('data');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/maintain/apply',
        type:'POST',
        dataType:'json',
        data:{
            id:id,
            active:obj.attr('status')
        },
        success:function(data){
            if (data==1){
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
}

/**
 * 上传文件的删除
 * @param obj
 * @private
 */
function _fileEdit(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/file/delete',
        type:'POST',
        dataType:'json',
        data:{
            status:status,
            id:id
        },
        success:function(data){
            if (data==1){
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
}

