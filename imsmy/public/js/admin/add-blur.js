var AddBlur = function() {
    // Init simple wizard, for more examples you can check out http://vadimg.com/twitter-bootstrap-wizard-example/
    var initAddBlur = function(){
        // Get forms
        var $form1 = jQuery('#form-blur-step1');
        var $form3 = jQuery('#form-blur-step3');
        var $form6 = jQuery('#form-blur-step6');

        jQuery.validator.addMethod("phone", function(value, element) {
            return this.optional(element) || /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/.test(value);
        }/*,"Please specify the correct phone for your documents" */);
        jQuery.validator.addMethod("image", function(value, element) {
            return  value != "";
        }/*,"Please specify the correct phone for your documents" */);

        $form1.on('keyup keypress', function (e) {
            var code = e.keyCode || e.which;

            if (code === 13) {
                e.preventDefault();
                return false;
            }
        });

        // Init form validation on classic wizard form
        var $validator1 = $form1.validate({
            errorClass: 'help-block text-right animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group > div').removeClass('has-error').addClass('has-error');
                jQuery(e).closest('.help-block').remove();
            },
            success: function(e) {
                jQuery(e).closest('.form-group > div').removeClass('has-error');
                jQuery(e).closest('.help-block').remove();
            },
            rules: {
                'name_zh': {
                    required: true
                },
                'name_en': {
                    required: true
                },
                'blur_class': {
                    required: true
                }
            },
            messages: {
                'name_zh': {
                    required:Lang.required_name_zh
                },
                'name_en': {
                    required: Lang.required_name_en
                },
                'blur_class': {
                    required: Lang.required_blur_class
                }
            }
        });

        var $validator3 = $form3.validate({
            errorClass: 'help-block animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.push-15-t').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.push-15-t').removeClass('has-error').addClass('has-error');
                jQuery(e).closest('.help-block').remove();
            },
            success: function(e) {
                jQuery(e).closest('.push-15-t').removeClass('has-error');
                jQuery(e).closest('.help-block').remove();
            }
        });

        var $validator6 = $form6.validate({
            errorClass: 'help-block animated fadeInDown col-md-offset-3',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('has-error').addClass('has-error');
                jQuery(e).closest('.help-block').remove();
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('has-error');
                jQuery(e).closest('.help-block').remove();
            },
            rules: {
                'audio': {
                    accept: 'audio/mp3'
                }
            },
            messages: {
                'audio': {
                    accept:Lang.mp3
                }
            }
        });

        jQuery('.js-wizard-blur').bootstrapWizard({
            'tabClass': '',
            'previousSelector': '.wizard-prev',
            'nextSelector': '.wizard-next',
            'finishSelector':'.wizard-finish',
            'onTabShow': function($tab, $navigation, $index) {
                var $total      = $navigation.find('li').length;
                var $current    = $index + 1;
                var $percent    = ($current/$total) * 100;

                // Get vital wizard elements
                var $wizard     = $navigation.parents('.block');
                var $progress   = $wizard.find('.wizard-progress > .progress-bar');
                var $btnPrev    = $wizard.find('.wizard-prev');
                var $btnNext    = $wizard.find('.wizard-next');
                var $btnFinish  = $wizard.find('.wizard-finish');

                // Update progress bar if there is one
                if ($progress) {
                    $progress.css({ width: $percent + '%' });
                }

                // If it's the last tab then hide the last button and show the finish instead
                if($current >= $total) {
                    $btnNext.hide();
                    $btnFinish.show();
                } else {
                    $btnNext.show();
                    $btnFinish.hide();
                }
            },
            'onNext': function($tab, $navigation, $index) {
                switch ($index){
                    case 1:
                        var $valid1 = $form1.valid();

                        if(!$valid1) {
                            $validator1.focusInvalid();

                            return false;
                        }
                        BlurForm.step1.submit();
                        return false;
                        break;
                    case 2:
                        if($('#selected_count').text() == 0){
                            $('#blur-step2_error')
                                .empty()
                                .append('<div class="help-block text-right animated fadeInDown">' +
                                    '<h5 class="text-danger">'+
                                        Lang.required_GPUImage +
                                    '</h5>'+
                                    '</div>');
                            return false;
                        }
                        break;
                    case 3:
                        var $valid3 = $form3.valid();

                        if(!$valid3) {
                            $validator3.focusInvalid();

                            return false;
                        }
                        BlurForm.step3.submit();
                        return false;
                        break;
                    case 5:
                        BlurForm.step5_submit();
                        return false;
                        break;
                }
            },
            'onTabClick': function() {
                return false;
            }
        });

        jQuery('.js-wizard-blur .wizard-finish').click(function(){
            var $valid6 = $form6.valid();

            if(!$valid6) {
                $validator6.focusInvalid();

                return false;
            }
            BlurForm.submit.submit();
        });
    };

    return {
        init: function () {
            initAddBlur();
        }
    };
}();

var Blur = function(){
    var initBlur = function(){
        //点击删除tag
        $(document).on("click","#blur-step2 .label > a",function(){
            var $parent = $(this).parent();
            var $value = $parent.attr('data-value');
            var $file = $('#range_' + GPUImage[$value - 1].id + '_file');
            $("#blur-step2 button[data-value=" + $value +"]").find('input').attr('checked',false);
            var _count = Number($('#selected_count').text());
            $('#selected_count').text(_count - 1);
            $parent.addClass('hidden');
            if($file.length > 0){
                $file.rules("remove","required accept");
            }
            $('#GPUImage_' + $value).remove();
        });

        //对应不支持纹理的GPUImage checkbox
        $(document).on("click","#blur-step2 button input[type=checkbox]",function(){
            var _value = $(this).parents('button').attr('data-value');
            var _count = Number($('#selected_count').text());
            $('#blur-step2_error').empty();
            if($(this).prop("checked")){
                $('#blur-step2 span[data-value='+ _value +']').removeClass('hidden');
                $('#selected_count').text(_count + 1);
                Blur.range(GPUImage[_value - 1]);
            }else{
                $('#blur-step2 span[data-value='+ _value +']').addClass('hidden');
                $('#selected_count').text(_count - 1);
                $('#GPUImage_' + _value).remove();
            }
        });

        //对应支持纹理的GPUImage radio
        $(document).on("click","#blur-step2 button input[type=radio]",function(){
            var _value = $(this).parents('button').attr('data-value');
            var _count = Number($('#selected_count').text());
            var $tag = jQuery('#blur-step2 span[data-value='+ _value +']');
            $('#blur-step2_error').empty();
            if($tag.hasClass('hidden')){
                $('.texture').each(function(){
                    if(!$(this).hasClass('hidden')){
                        _count = Number($('#selected_count').text());
                        $(this).addClass('hidden');
                        $('#selected_count').text(_count - 1);
                        $('#GPUImage_' + $(this).attr('data-value')).remove();
                    }
                });
                _count = Number($('#selected_count').text());
                $tag.removeClass('hidden');
                $('#selected_count').text(_count + 1);
                Blur.range(GPUImage[_value - 1]);
                $('#range_' + GPUImage[_value - 1].id + '_file').rules("add", {
                    required: true,
                    accept: "image/jpeg,image/png",
                    messages:{
                        required:Lang.required_image,
                        accept:Lang.jpg_png
                    }
                });
            }else{
                $(this).attr('checked',false);
                $tag.addClass('hidden');
                $('#selected_count').text(_count - 1);
                $('#range_' + GPUImage[_value - 1].id + '_file').rules("remove","required accept");
                $('#GPUImage_' + _value).remove();
            }
        });

        //
        jQuery(document).on('click','#blur-step3 .reset-data',function(e){
            var _id = $(this).attr('data-id');
            var _value = $(this).attr('data-value');
            var slider = $('#range_' + _id).data('ionRangeSlider');
            slider.update({
                from: _value
            });
            $('#range_' + _id + '_file').val(null);
        });

        jQuery(document).on('click','#gravity',function(){
            if($(this).prop("checked")){
                $(this).parent().children('strong').text(Lang.gravity_open);
            }else{
                $(this).parent().children('strong').text(Lang.gravity_close);
            }
        });
    };
    var range = function(item){
        var _disable = false;
        var item_value = item.has_many_g_p_u_image_value;
        var append = function(item){
            var $step = jQuery('#form-blur-step3');
            var html = "";
            html += '<div class="row border-b push-30-t" id="GPUImage_'+ item.id +'">';
            html +=     (item.texture ?'<div class="col-md-8 col-md-offset-1">' : '<div class="col-md-8 col-md-offset-2">');
            for(var i in item_value){
                var data = item_value[i];
                _disable = false;
                if(null == data.min || null == data.max || null == data.init){
                    _disable = true;
                    data.min = 0;
                    data.max = 0;
                    data.init = 0;
                 }
                html += '<div class="row">' +
                            '<div class="col-md-6">'+
                                '<div class="form-material">'+
                                    '<input class="form-control" type="text" id="range_'+ data.id +'" name="'+ data.id +'">'+
                                    '<label for="range_'+ data.id +'">'+
                                        ((item.name_zh == data.name_zh) ? data.name_zh :  item.name_zh +' -- '+ data.name_zh) +
                                    '</label>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-4">'+
                                '<input class="form-control push-10-t remove-padding-l remove-padding-r text-center"'+
                                    ' id="range_'+ data.id +'_input"  value="'+ data.init +'"' +
                                    (_disable ? 'readonly' : '')+'>' +
                            '</div>' +
                            '<div class="col-md-2">'+
                                '<div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="'+ data.id +'" data-value="'+ data.init +'"></i></div>'+
                            '</div>'+
                        '</div>';

            }
            html += '</div>';
            if(item.texture){
                html += '<div class="col-md-2">'+
                            '<div class="push-15-t"><input type="file" id="range_'+ item.id +'_file" name="'+ item.id +'_file"></div>'+
                        '</div>';
            }
            html += '</div>';
            $step.append(html);
        };

        var show = function(){
            _disable = false;
            var $slider = jQuery('#range_' + data.id);
            var $input = jQuery('#range_'+ data.id +'_input');
            if(null == data.min || null == data.max || null == data.init){
                _disable = true;
                data.min = 0;
                data.max = 0;
                data.init = 0;
            }
            $slider.ionRangeSlider({
                type:"single",
                min:data.min,
                max:data.max,
                from:data.init,
                keyboard:true,
                step:0.001,
                disable:_disable,
                onStart:function(data){
                    $input.val(data.from);
                },
                onChange:function(data){
                    $input.val(data.from);
                },
                onUpdate:function(data){
                    $input.val(data.from)
                }
            });
            $input.on('keydown',{$slider:$slider,$input:$input}, function( e ) {
                var slider = e.data.$slider.data('ionRangeSlider');

                // 13 is enter,
                // 38 is key up,
                // 40 is key down.
                switch ( e.which ) {
                    case 13:
                        var value = e.data.$input.val();
                        range(e,slider,value);
                        break;
                    case 38:
                        var value = Number(e.data.$input.val()) + Number(slider.options.step);
                        range(e,slider,value);
                        break;
                    case 40:
                        var value = Number(e.data.$input.val()) - Number(slider.options.step);
                        range(e,slider,value);
                        break;
                }
            });
            var range = function(e,slider,value){
                value = value > slider.options.max ? slider.options.max : value;
                value = value > slider.options.min ? value : slider.options.min;
                slider.update({
                    from:value
                });
            };
        }


        append(item);
        for(var i in item_value){
            var data = item_value[i];
            show(data);
        }


    };

    /*var range = function(data){
        var _id = data.id;
        var _disable = false;
        var append = function(data){
            var $step = jQuery('#form-blur-step3');
            if(null == data.min || null == data.max || null == data.init){
                _disable = true;
                data.min = 0;
                data.max = 0;
                data.init = 0;
            }
            var html = "";
            html += '<div class="row border-b push-30-t" id="GPUImage_'+ data.id +'">';
            html +=     (data.texture ?'<div class="col-md-5 col-md-offset-1">' : '<div class="col-md-5 col-md-offset-2">');
            html +=         '<div class="form-material">'+
                '<input class="form-control" type="text" id="range_'+ data.id +'" >'+
                '<label for="range_'+ data.id +'">'+ data.name_zh +'</label>'+
                '</div>'+
                '</div>'+
                '<div class="col-md-2 col-md-offset-0 col-xs-6 col-xs-offset-2">';
            if(_disable){
                html +=     '<input class="form-control push-10-t remove-padding-l remove-padding-r text-center" id="range_'+ data.id +'_input" name="'+ data.id +'" value="'+ data.init +'" readonly>';
            }else{
                html +=     '<input class="form-control push-10-t remove-padding-l remove-padding-r text-center" id="range_'+ data.id +'_input" name="'+ data.id +'" value="'+ data.init +'">';
            }
            html +=     '</div>'+
                '<div class="col-md-1 col-xs-4">'+
                '<div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="'+ data.id +'" data-value="'+ data.init +'"></i></div>'+
                '</div>';
            if(data.texture){
                html += '<div class="col-md-2 col-md-offset-0 col-xs-8 col-xs-offset-2">'+
                    '<div class="push-15-t"><input type="file" id="range_'+ data.id +'_file" name="'+ data.id +'_file"></div>'+
                    '</div>';
            }
            html += '</div>';
            $step.append(html);
        };


        append(data);
        var $slider = jQuery('#range_' + _id);
        var $input = jQuery('#range_'+ _id +'_input');
        $slider.ionRangeSlider({
            type:"single",
            min:data.min,
            max:data.max,
            from:_value,
            keyboard:true,
            step:0.001,
            disable:_disable,
            onStart:function(data){
                $input.val(data.from);
            },
            onChange:function(data){
                $input.val(data.from);
            },
            onUpdate:function(data){
                $input.val(data.from)
            }
        });
        $input.on('keydown',{$slider:$slider,$input:$input}, function( e ) {
            var slider = e.data.$slider.data('ionRangeSlider');

            // 13 is enter,
            // 38 is key up,
            // 40 is key down.
            switch ( e.which ) {
                case 13:
                    var value = e.data.$input.val();
                    range(e,slider,value);
                    break;
                case 38:
                    var value = Number(e.data.$input.val()) + Number(slider.options.step);
                    range(e,slider,value);
                    break;
                case 40:
                    var value = Number(e.data.$input.val()) - Number(slider.options.step);
                    range(e,slider,value);
                    break;
            }
        });
        var range = function(e,slider,value){
            value = value > slider.options.max ? slider.options.max : value;
            value = value > slider.options.min ? value : slider.options.min;
            slider.update({
                from:value
            });
        };
    };*/
    return {
        init : function(){
            initBlur()
        },
        range :function(data){
            range(data);
        }
    }
}();

/**
 * jquery ajax form
 */
var BlurForm = function(){
    var $btnNext    = $('.wizard-next');
    var $btnFinish  = $('.wizard-finish');

    var step1 = function(){
        var options = {
            beforeSubmit    :   showRequest,
            success         :   showResponse,
            dataType        :   'json'
        };

        var submit = function(){
            if($btnNext.hasClass('disabled')){
                return false;
            }
            $btnNext.html(Lang.loading);
            $btnNext.addClass('disabled');
            $('#form-blur-step1').ajaxForm(options).submit();
        };

        function showRequest(){
            return true;
        }

        function showResponse(data){
            $btnNext.html(Lang.next);
            $btnNext.removeClass('disabled');
            if(data.success == false){
                if(data.error.en !== 0){
                    $('#name_en_error')
                        .empty()
                        .append('<div class="help-block text-right animated fadeInDown">' +
                            '<span class="text-danger">'+
                                '<span>"'+
                                  data.error.name_en +
                                '"</span>'+
                                Lang.existed +
                            '</span>'+
                            '</div>');
                }
                if(data.error.zh !== 0){
                    $('#name_zh_error')
                        .empty()
                        .append('<div class="help-block text-right animated fadeInDown">' +
                                    '<span class="text-danger">'+
                                        '<span>"'+
                                            data.error.name_zh +
                                        '"</span>'+
                                        Lang.existed +
                                    '</span>'+
                                '</div>');
                }
                return false;
            }
            jQuery('.js-wizard-blur').bootstrapWizard('show',1);
        }

        return  {
            submit :submit
        }

    }();

    var step3 = function(){
        var options = {
            beforeSubmit    :   showRequest,
            success         :   showResponse,
            dataType        :   'json'
        };
        var submit = function(){
            if($btnNext.hasClass('disabled')){
                return false;
            }
            $btnNext.html(Lang.loading);
            $btnNext.addClass('disabled');
            $('#form-blur-step3').ajaxForm(options).submit();
        };

        function showRequest(){
            return true;
        }

        function showResponse(){
            $btnNext.html(Lang.next);
            $btnNext.removeClass('disabled');
            jQuery('.js-wizard-blur').bootstrapWizard('show',3);
        }

        return  {
            submit :submit
        }
    }();

    var step4 = function(){

        //重力感应图片
        var gravity = new Dropzone("#blur-step4-gravity", {
            url: "/admin/app/camera/blur/add/gravity_image",
            maxFilesize:2,
            acceptedFiles:"image/*",
            addRemoveLinks:"delete",
            //default or error msg
            dictDefaultMessage              :   Lang.dictDefaultMessage,
            dictInvalidFileType             :   Lang.dictInvalidFileType,
            dictFileTooBig                  :   Lang.dictFileTooBig,
            dictCancelUpload                :   Lang.dictCancelUpload,
            dictCancelUploadConfirmation    :   Lang.dictCancelUploadConfirmation,
            dictRemoveFile                  :   Lang.dictRemoveFile
        });

        gravity.on('removedfile',function(file){
            if(file.status == 'success'){
                $.ajax( {
                    url:'/admin/app/camera/blur/add/gravity_image/delete',
                    data:{
                        name : file.name,
                        key  : $('meta[name="_key"]').attr('content')
                    },
                    type:'post',
                    dataType:'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
            }
        });
        //重力感应图片 结束

        //人脸追踪图片
        var face = new Dropzone("#blur-step4-face",{
            url:"/admin/app/camera/blur/add/face_image",
            maxFiles:1,
            maxFilesize:2,
            acceptedFiles:"image/*",
            addRemoveLinks:"delete",
            //default or error msg
            dictDefaultMessage              :   Lang.dictDefaultMessage,
            dictInvalidFileType             :   Lang.dictInvalidFileType,
            dictFileTooBig                  :   Lang.dictFileTooBig,
            dictCancelUpload                :   Lang.dictCancelUpload,
            dictCancelUploadConfirmation    :   Lang.dictCancelUploadConfirmation,
            dictRemoveFile                  :   Lang.dictRemoveFile,
            dictMaxFilesExceeded            :   Lang.dictMaxFilesExceeded
        });
        //人脸追踪图片 结束

        face.on('removedfile',function(file){
            if(file.status == 'success'){
                $.ajax( {
                    url:'/admin/app/camera/blur/add/face_image/delete',
                    data:{
                        name : file.name,
                        key  : $('meta[name="_key"]').attr('content')
                    },
                    type:'post',
                    dataType:'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
            }
        });
    };

    var step5= function(){
        var init = function(){
            //人脸追踪图片
            var dynamic = new Dropzone("#blur-step5-dynamic",{
                url:"/admin/app/camera/blur/add/dynamic_image",
                maxFiles:1,
                maxFilesize:2,
                acceptedFiles:"image/gif",
                addRemoveLinks:"delete",
                previewTemplate: document.getElementById('preview-template').innerHTML,
                thumbnailWidth:90,
                thumbnailHeight:120,
                //default or error msg
                dictDefaultMessage              :   Lang.dictDefaultMessage,
                dictInvalidFileType             :   Lang.dictInvalidFileType,
                dictFileTooBig                  :   Lang.dictFileTooBig,
                dictCancelUpload                :   Lang.dictCancelUpload,
                dictCancelUploadConfirmation    :   Lang.dictCancelUploadConfirmation,
                dictRemoveFile                  :   Lang.dictRemoveFile,
                dictMaxFilesExceeded            :   Lang.dictMaxFilesExceeded
            });
            //人脸追踪图片 结束

            dynamic.on('removedfile',function(file){
                if(file.status == 'success'){
                    $.ajax( {
                        url:'/admin/app/camera/blur/add/dynamic_image/delete',
                        data:{
                            name : file.name,
                            key  : $('meta[name="_key"]').attr('content')
                        },
                        type:'post',
                        dataType:'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                }
            });

            //成功上传，从服务器获取图片，以链接形式显示
            dynamic.on("success", function(file, responseText) {
                // Handle the responseText here. For example, add the text to the preview element:
                $this = jQuery(file.previewTemplate);
                $this.find('.dz-view a').attr('href',responseText);
            });

            //失败，链接删除
            dynamic.on("error", function(file, responseText) {
                $this = jQuery(file.previewTemplate);
                $this.find('.dz-view a').remove();
            });
            //人脸追踪图片 结束

            //背景图 上传
            var background = new Dropzone("#blur-step5-background",{
                url:"/admin/app/camera/blur/add/background_image",
                maxFiles:1,
                maxFilesize:5,
                acceptedFiles:"image/png,image/jpeg,image/gif",
                addRemoveLinks:"delete",
                thumbnailWidth:90,
                thumbnailHeight:120,
                //default or error msg
                dictDefaultMessage              :   Lang.dictDefaultMessage,
                dictInvalidFileType             :   Lang.dictInvalidFileType,
                dictFileTooBig                  :   Lang.dictFileTooBig_5,
                dictCancelUpload                :   Lang.dictCancelUpload,
                dictCancelUploadConfirmation    :   Lang.dictCancelUploadConfirmation,
                dictRemoveFile                  :   Lang.dictRemoveFile,
                dictMaxFilesExceeded            :   Lang.dictMaxFilesExceeded
            });

            background.on('removedfile',function(file){
                if(file.status == 'success'){
                    $.ajax( {
                        url:'/admin/app/camera/blur/add/background_image/delete',
                        data:{
                            name : file.name,
                            key  : $('meta[name="_key"]').attr('content')
                        },
                        type:'post',
                        dataType:'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                }
            });

            background.on('success',function(){
                jQuery('#background-error').addClass('hidden');
            });
        };

        var submit = function(){
            var options = {
                beforeSubmit    :   showRequest,
                success         :   showResponse,
                dataType        :   'json'

            };
            var submit = function(){
                if($btnNext.hasClass('disabled')){
                    return false;
                }
                $btnNext.html(Lang.loading);
                $btnNext.addClass('disabled');
                $('#form-blur-step5').ajaxForm(options).submit();
            };

            function showRequest(){
                return true;
            }

            function showResponse(data){
                $btnNext.html(Lang.next);
                $btnNext.removeClass('disabled');
                if(data.success == true){
                    jQuery('.js-wizard-blur').bootstrapWizard('show',5);
                }else{
                    jQuery('#background-error').removeClass('hidden');
                }

            }
            return {
                submit:submit
            }
        }();
        return {
            init : init,
            submit : submit.submit
        }

    }();

    var step6 = function(){
        //快门速度
        var $shutter = jQuery('#shutter_speed');
        $shutter.ionRangeSlider({
            type:"single",
            keyboard:true,
            values: [
                "auto", "1/2", "1/4",
                "1/5", "1/6", "1/8",
                "1/10", "1/13", "1/15",
                "1/20", "1/25", "1/40",
                "1/50", "1/60"
            ],
            grid: true
        });
        //快门速度结束

        var _data = [
            {
                id:'xAlign',
                min:0,
                max:414,
                from:0,
                step:0.01
            },
            {
                id:'yAlign',
                min:0,
                max:736,
                from:0,
                step:0.01
            },
            {
                id:'scale',
                min:0,
                max:100,
                from:100,
                step:1
            }
        ];
        var slider = function(data){
            var $slider = jQuery('#' + data.id);
            var $slider_input = jQuery('#' + data.id + '_input');
            $slider.ionRangeSlider({
                type:"single",
                keyboard:true,
                min:data.min,
                max:data.max,
                from:data.from,
                step:data.step,
                onStart:function(data){
                    $slider_input.val(data.from);
                },
                onChange:function(data){
                    $slider_input.val(data.from);
                },
                onUpdate:function(data){
                    $slider_input.val(data.from)
                }
            });
            $slider_input.on('keydown',{$slider:$slider,$input:$slider_input}, function( e ) {
                var slider = e.data.$slider.data('ionRangeSlider');

                // 13 is enter,
                // 38 is key up,
                // 40 is key down.
                switch ( e.which ) {
                    case 13:
                        var value = e.data.$input.val();
                        range(e,slider,value);
                        break;
                    case 38:
                        var value = Number(e.data.$input.val()) + Number(slider.options.step);
                        range(e,slider,value);
                        break;
                    case 40:
                        var value = Number(e.data.$input.val()) - Number(slider.options.step);
                        range(e,slider,value);
                        break;
                }
            });
        };

        for(var i in _data){
            slider(_data[i]);
        }
        var range = function(e,slider,value){
            value = value > slider.options.max ? slider.options.max : value;
            value = value > slider.options.min ? value : slider.options.min;
            slider.update({
                from:value
            });
        };


        jQuery(document).on('click','#blur-step6 .reset-data',function(e){
            var _id = $(this).attr('data-id');
            var _value = $(this).attr('data-value');
            var slider = $('#' + _id).data('ionRangeSlider');
            slider.update({
                from: _value
            });
        });
        //提交第六步 并整体保存跳转

    };

    var submit = function(){
        var options = {
            beforeSubmit    :   showRequest,
            success         :   showResponse,
            dataType        :   'json'
        };

        var submit = function(){
            if($btnFinish.hasClass('disabled')){
                return false;
            }
            $btnFinish.html(Lang.loading);
            $btnFinish.addClass('disabled');
            $('#form-blur-step6').ajaxForm(options).submit();
        };

        function showRequest(){
            return true;
        }

        function showResponse(){
            window.location.href= window.location.origin + '/admin/app/camera/blur?active=2';
        }

        return  {
            submit :submit
        }
    }();

    return {
        step1 : step1,

        step3 : step3,

        step4 : function(){
            step4();
        },

        step5_init : function(){
            step5.init();
        },

        step5_submit :function(){
            step5.submit();
        },

        step6 : function(){
            step6();
        },

        submit : submit
    }
}();
jQuery(function(){
    AddBlur.init();

    Blur.init();

    Dropzone.autoDiscover = false;
    BlurForm.step4();

    BlurForm.step5_init();

    BlurForm.step6();
});