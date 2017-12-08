$(document).ready(function() {
    var options = {
        beforeSubmit:  showRequest,
        success:       showResponse,
        dataType: 'json'
    };
    //Bug修改 : 不刷新情况下，非第一次上传用一个文件后无反应
    //原因 : 同一个文件，前台无法调用change事件
    //解决 : 点击时，清空input
    //链接 : http://stackoverflow.com/questions/12030686/html-input-file-selection-event-not-firing-upon-selecting-the-same-file
    $('#upload-image')
        .on('click',function(){
            this.value = null;
        })
        .on('change', function(){
            $('#upload-avatar').html(Lang.uploading);
            $('#upload').ajaxForm(options).submit();
        });
});
function showRequest() {
    $("#validation-errors").hide().empty();
    $("#output").css('display','none');
    return true;
}

function showResponse(response)  {
    if(response.success == false)
    {
        var responseErrors = response.errors;
        $.each(responseErrors, function(index, value)
        {
            if (value.length != 0)
            {
                $("#validation-errors").append('<div class="text-center text-danger"><strong>'+ value +'</strong><div>');
            }
        });
        $("#validation-errors").show();
        $('#upload-avatar').html(Lang.uploadNewAvatar);
    } else {
        var cropBox = $("#cropbox");
        cropBox.attr('src',response.avatar);
        $("#photo").val(response.avatar);
        $("#upload-avatar").html(Lang.uploadNewAvatar);
        $("#exampleModal").modal('show');
        cropBox.Jcrop({
            aspectRatio:1,
            onSelect:updateCoords,
            setSelect:[120,120,10,10],
            keySupport: false,
            addClass:'center-block'
        });
        $('.jcrop-holder img').attr('src',response.avatar);
        /*$('#user-avatar').attr('src',response.avatar);*/

        //添加的两个function
        function updateCoords(c)
        {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
        }
        function checkCoords()
        {
            if (parseInt($('#w').val())) return true;
            alert('请选择图片.');
            return false;
        }
    }

}
