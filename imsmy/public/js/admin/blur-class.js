var BlurClass = function() {
    // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidation = function(){
        jQuery.validator.addMethod("icon", function(value, element) {
            return  ($('#edit-blur-class-page').length && $("#is_clear").val() == 0)
                    || value != "";
        });

        jQuery('.js-validation-blur-class').validate({
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
                'name-zh': {
                    required: true
                },
                'name-en': {
                    required: true
                },
                'blur-class-icon-sm': {
                    icon:true
                },
                'blur-class-icon-lg': {
                    icon:true
                }
            },
            messages: {
                'name-zh':{
                    required:Lang.name_zh_required
                },
                'name-en':{
                    required:Lang.name_en_required
                },
                'blur-class-icon-sm':{
                    icon:Lang.icon_required
                },
                'blur-class-icon-lg':{
                    icon:Lang.icon_required
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