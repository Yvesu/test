/**
 * Created by Administrator on 2016/5/19.
 */
var Channel = function() {
    // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidation = function(){
        jQuery.validator.addMethod("icon", function(value, element) {
            return  $('#channel-flag').attr('data-value') || value != "";
        });

        jQuery('.js-validation-channel').validate({
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
                },
                'channel-icon': {
                    icon: true
                }
            },
            messages: {
                'name':{
                    required:Lang.name_required
                },
                'channel-icon':{
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
