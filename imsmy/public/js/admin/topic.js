/**
 * Created by Administrator on 2016/5/19.
 */
var Topic = function() {
    // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidation = function(){
        jQuery.validator.addMethod("text", function(value, element) {
            return /^[a-z0-9\u4e00-\u9fa5]+$/igm.test(value);
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
            rules: {
                'name': {
                    required: true,
                    text: true
                },
                'username': {
                    required: true,
                    number: true,
                    minlength: 11,
                    maxlength: 11
                },
                'password': {
                    required: true,
                    text: true,
                    minlength: 6,
                    maxlength: 18
                },
                'nickname': {
                    required: true,
                    text: true
                }
            },
            messages: {
                'name':{
                    required:Lang.name_required,
                    text: Lang.text
                },
                'username':{
                    required:Lang.username_required,
                    number: '必须全为数字',
                    minlength: '密码必须11位',
                    maxlength: '密码必须11位'
                },
                'password':{
                    required:Lang.password_required,
                    text: Lang.text,
                    minlength: '密码必须6位数以上',
                    maxlength: '密码必须18位以下'
                },
                'nickname':{
                    required:'用户昵称不能为空',
                    text: Lang.text
                }
            }
        });
    };

    var init = function () {

    };

    return {
        init: function () {
            // Init Login Form Validation
            initValidation();
        }
    };
}();

