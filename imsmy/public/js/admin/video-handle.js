/**
 * 视频审批时的屏蔽与通过选择
 * @param obj
 * @private
 */
function _videoHandle(obj){
    var id = obj.attr('data');
    var channel_id = $('#channel_id').val();
    $.get('/admin/video/apply',{
        id:id,
        active:obj.attr('status'),
        channel_id:channel_id
    },function(data){
        if (data==1){
            layer.msg('操作成功');
            $('.videoCheck>button[data='+id+']').parents('tr.divStatus').remove();
        }else{
            layer.msg('操作失败');
        }
    },'json');
}

/**
 * 视频详情页 状态操作
 * @param obj
 * @private
 */
function _videoActive(obj){
    var id = obj.attr('data');
    $.get('/admin/video/apply',{
        id:id,
        active:obj.attr('status'),
    },function(data){
        if (data==1){
            layer.msg('操作成功');
            $('.video-edit>button[data='+id+']').remove();
        }else{
            layer.msg('操作失败');
        }
    },'json');
}

