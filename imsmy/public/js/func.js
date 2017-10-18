
/**
 * 封装 layer.tips方法指定样式位置  getTips
 * @param  content    提示框内容
 */
$.fn.getTips = function(content){
    layer.tips(content,$(this),{
        tips:[3, '#0FA6D8'],
    });
}

/**
 * 封装 layer.msg方法指定样式位置  getMsg
 * @param  content  弹出框的内容
 */
function getMsg(content){
    layer.msg(content, {time: 2000, icon:6});
}