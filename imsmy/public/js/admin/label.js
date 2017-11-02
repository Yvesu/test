/**
 * Created by Administrator on 2016/5/31.
 */
var Label = function() {
    // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidation = function(){
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
                    required: true
                }
            },
            messages: {
                'name':{
                    required:Lang.name_required
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