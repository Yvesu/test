var AddAdmin = function() {
    // Init simple wizard, for more examples you can check out http://vadimg.com/twitter-bootstrap-wizard-example/
    var initAddAdmin = function(){
        // Get forms
        var $form1 = jQuery('.js-form');

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
            errorClass: 'help-block animated fadeInDown',
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
                'add-admin-email': {
                    required: true,
                    email:true
                },
                'add-admin-phone': {
                    required: true,
                    phone: true
                },
                'add-admin-name': {
                    required: true
                },
                'add-admin-sex': {
                    required: true
                },
                'add-admin-department': {
                    required: true
                },
                'add-admin-position': {
                    required: true
                },
                'add-admin-secondary-contact-name':{
                    required:true
                },
                'add-admin-secondary-contact-phone':{
                    required:true,
                    phone:true
                },
                'add-admin-secondary-contact-relationship':{
                    required:true
                },
                'add-admin-IDCard':{
                    image:true
                },
                'add-admin-user-id':{
                    digits: true
                }
            },
            messages: {
                'add-admin-email': {
                    required: Lang.email_required,
                    email: Lang.email_correct
                },
                'add-admin-phone': {
                    required: Lang.phone_required,
                    phone: Lang.phone_correct
                },
                'add-admin-name' :{
                    required: Lang.name_required
                },
                'add-admin-sex' :{
                    required: Lang.sex_required
                },
                'add-admin-department' :{
                    required: Lang.department_required
                },
                'add-admin-position' :{
                    required: Lang.position_required
                },
                'add-admin-secondary-contact-name':{
                    required:Lang.secondary_contact_name_required
                },
                'add-admin-secondary-contact-phone':{
                    required:Lang.secondary_contact_phone_required,
                    phone: Lang.phone_correct
                },
                'add-admin-secondary-contact-relationship':{
                    required:Lang.secondary_contact_relationship_required
                },
                'add-admin-IDCard':{
                    image:Lang.IDCard_required
                },
                'add-admin-user-id':{
                    digits: Lang.APP_user_id
                }
            }
        });

        jQuery('.js-wizard-simple').bootstrapWizard({
            'tabClass': '',
            'previousSelector': '.wizard-prev',
            'nextSelector': '.wizard-next',
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
                var $valid = $form1.valid();

                if(!$valid) {
                    $validator1.focusInvalid();

                    return false;
                }

                if($index == 2){
                    $('.user-id-error').addClass('hidden');
                    var $id = $('#add-admin-user-id').val();
                    checkUserID($id);
                    return false
                }
            },
            onTabClick: function($tab, $navigation, $index) {
                return false;
            }
        });
    };

    var checkUserID = function($id) {
        $.get("/admin/userID?user=" + $id, function(result){
            console.log('checkUserID:' + result);

            if(result !== '0'){
                $('.user-id-error').removeClass('hidden');
            } else {
                jQuery('.js-wizard-simple').bootstrapWizard('show',2);
            }
        });
    };
    return {
        init: function () {
            initAddAdmin();
        }
    };
}();

// Initialize when page loads
jQuery(function(){ AddAdmin.init(); });