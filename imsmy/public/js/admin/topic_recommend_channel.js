/**
 * Created by Administrator on 2016/6/7.
 */

var Topic_Recommend_Channel = function () {
    if(1 == $('.switch.switch-success > input[type=checkbox]').val()){
        $('#recommend').removeClass('hidden');
    }
    var init = function () {
        $(document).on('change','.switch.switch-success > input[type=checkbox]',function(){
            if (this.checked) {
                $('#' + this.dataset.value).removeClass('hidden');
            } else {
                $('#' + this.dataset.value).addClass('hidden');
            }
        });

        $select_multi = $(".js-example-basic-multiple");
        $select_multi.select2({
            selectOnClose: false
        });
        $('.datepicker').datepicker({
            startDate: new Date().toLocaleDateString()
        });
        $('input[name=_timezone]').val((new Date()).getTimezoneOffset());

        show_data('recommend');
        $channels = $('#channels > option');
        $tweet_channels = Lang.topic_data.has_many_channel;
        var channels_arr = [];
        for (var i in $tweet_channels) {
            for (var j in $channels) {
                if($tweet_channels[i].id == $channels[j].value){
                    channels_arr.push($tweet_channels[i].id.toString());
                }
            }
        }
        console.log(channels_arr);
        $select_multi.select2().val(channels_arr).trigger("change");
    };

    var initValidation = function(){
        jQuery.validator.addMethod("time", function(value, element) {
            var timezone = (new Date()).getTimezoneOffset();
            var name = element.id.substring(0,element.id.indexOf('_')) + '_date';
            var date = $('#' + name).val();
            var timestamp = new Date(date + 'T' + value + ':00');
            var now = Date.now();
            if(timestamp == 'Invalid Date' || now > timestamp.getTime() + timezone * 60000) {
                return false;
            }
            return true;
        });

        jQuery('.js-validation-channel').validate({
            errorClass: 'margin-top-30 help-block text-right animated fadeInDown',
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
                'recommend_date': {
                    required: true
                },
                'recommend_time': {
                    time: true
                }
            },
            messages: {
                'recommend_date':{
                    required: Lang.date_invalid
                },
                'recommend_time':{
                    time: Lang.time_invalid
                }
            }
        });
    };

    var show_data = function(name){
        var expire = name + '_expires';
        if(Lang.topic_data[expire] != null) {
            var time = Goobird.dateToUnix(Lang.topic_data[expire]);
            if(time.getTime() > Date.now()) {
                var date_month = (time.getMonth() + 1) < 10 ? '0' + (time.getMonth() + 1) : time.getMonth() + 1;
                var date_day =  time.getDate() < 10 ? '0' +  time.getDate() :  time.getDate();
                $('#' + name + '_date').val(time.getFullYear() + '-' + date_month + '-' + date_day);
                var time_hours = time.getHours() < 10 ? '0' + time.getHours() : time.getHours();
                var time_minutes = time.getMinutes() < 10 ? '0' + time.getMinutes() : time.getMinutes();
                $('#' + name + '_time').val(time_hours + ':' + time_minutes);
                $('#' + name + '_check').prop('checked',true);
                $('#' + name).removeClass('hidden');
            }
        }
    };

    return {
        init: function () {
            init();
            initValidation();
        }
    }
}();

jQuery(function () {

    Topic_Recommend_Channel.init();
});