/**
 * 用户角色类型的排序修改
 * @param obj
 * @private
 */
function _roleTypeSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/user/role/sort',
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

/**
 * 影片种类的排序修改
 * @param obj
 * @private
 */
function _filmTypeSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/config/film/sort',
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

/**
 * 音频--音乐--文件夹的排序修改、激活、删除
 * @param obj
 * @private
 */
function _audioFolderSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/audio/folder/sort',
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

/**
 * 音频--音乐--文件激活、删除
 * @param obj
 * @private
 */
function _audioFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/audio/file/sort',
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

/**
 * 音频--音效--文件夹的排序修改、激活、删除
 * @param obj
 * @private
 */
function _audioEffectFolderSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/audio/effect/folder/sort',
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

/**
 * 音频--音效--文件激活、删除
 * @param obj
 * @private
 */
function _audioEffectFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/audio/effect/file/sort',
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

/**
 * 贴图文件夹的排序修改、激活、删除
 * @param obj
 * @private
 */
function _chartletFolderSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/chartlet/folder/sort',
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

/**
 * 贴图文件激活、删除
 * @param obj
 * @private
 */
function _chartletFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/chartlet/file/sort',
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

/**
 * 效果文件夹的排序修改、激活、删除
 * @param obj
 * @private
 */
function _effectsFolderSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/effects/folder/sort',
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

/**
 * 效果文件激活、删除
 * @param obj
 * @private
 */
function _effectsFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/effects/file/sort',
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

/**
 * 字体种类的排序修改
 * @param obj
 * @private
 */
function _fontFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/font/file/sort',
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

/**
 * 滤镜文件夹的排序修改、激活、删除
 * @param obj
 * @private
 */
function _filterFolderSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/filter/folder/sort',
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

/**
 * 滤镜文件激活、删除
 * @param obj
 * @private
 */
function _filterFileSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/make/filter/file/sort',
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

/**
 * 创作页面的封面 激活、删除
 * @param obj
 * @private
 */
function _creationCoverSort(obj){

    var id = obj.attr('value');
    var status = obj.attr('status');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:'/admin/creation/cover/sort',
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