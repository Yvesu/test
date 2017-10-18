/**
 * Created by Administrator on 2016/5/25.
 */
var Video = function() {
    // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidation = function(){
        jQuery.validator.addMethod("upload", function(value, element) {
            return value;
        });

        jQuery.validator.addMethod("screen_shot", function(value, element) {
            return  ($('#video-flag').attr('data-value') != 0) || value != "";
        });

        jQuery('.js-validation-topic').validate({
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
            ignore: "",
            rules: {
                'name': {
                    required: true
                },
                'key' : {
                    upload: true
                },
                'video-icon': {
                    screen_shot:true
                }
            },
            messages: {
                'name':{
                    required:Lang.name_required
                },
                'key' : {
                    upload: Lang.video_required
                },
                'video-icon': {
                    screen_shot:Lang.screen_shot_required
                }
            }
        });
    };

    return {
        init: function () {
            // Init Login Form Validation
            initValidation();
        }
    };
}();