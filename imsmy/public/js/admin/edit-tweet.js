/**
 * Created by Administrator on 2016/6/6.
 */
var editTweet = function(){
    var initt = function(){
        $(document).on('change','.switch.switch-success > input[type=checkbox]',function(){
            if (this.checked) {
                $('#' + this.dataset.value).removeClass('hidden');
            } else {
                $('#' + this.dataset.value).addClass('hidden');
            }
        });

        show_data('top');
        show_data('recommend');
        //$labels = $('#label_name > option');
        //for(var i in $labels){
        //    if($labels[i].value == Lang.tweet_data.label_id){
        //        $(".js-example-basic").select2({minimumResultsForSearch: Infinity}).val($labels[i].value).trigger("change");
        //        show_data('label');
        //        break;
        //    }
        //}

        // 频道
        $channels = $('#channels > option');
        $tweet_channels = Lang.tweet_data.belongs_to_many_channel;
        var channels_arr = [];
        for (var i in $tweet_channels) {
            for (var j in $channels) {
                if($tweet_channels[i].id == $channels[j].value){
                    channels_arr.push($tweet_channels[i].id.toString());
                }
            }
        }
        //console.log(channels_arr);
        $(".js-example-basic-multiple").select2().val(channels_arr).trigger("change");


        // 话题
        $topics = $('#topics > option');
        $tweet_topics = Lang.topic_data;
        var topics_arr = [];
        for (var i in $tweet_topics) {
            for (var j in $topics) {
                if($tweet_topics[i].id == $topics[j].value){
                    topics_arr.push($tweet_topics[i].id.toString());
                }
            }
        }
        //console.log(topics_arr);
        $(".js-example-basic-multiple-topic").select2().val(topics_arr).trigger("change");


        // 活动
        $activities = $('#activities > option');
        $activity_topics = Lang.activity_data;
        var activities_arr = [];
        for (var i in $activity_topics) {
            for (var j in $activities) {
                if($activity_topics[i].id == $activities[j].value){
                    activities_arr.push($activity_topics[i].id.toString());
                }
            }
        }
        //console.log(topics_arr);
        $(".js-example-basic-multiple-activity").select2().val(activities_arr).trigger("change");

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
                'top_date': {
                    required: true
                },
                'top_time': {
                    time: true
                },
                'recommend_date': {
                    required: true
                },
                'recommend_time': {
                    time: true
                },
                'label_date': {
                    required: true
                },
                'label_time': {
                    time: true
                },
                'number': {
                    required: true,
                    text: true,
                    minlength: 6,
                }
            },
            messages: {
                'top_date':{
                    required: Lang.date_invalid
                },
                'top_time': {
                    time: Lang.time_invalid
                },
                'recommend_date':{
                    required: Lang.date_invalid
                },
                'recommend_time':{
                    time: Lang.time_invalid
                },
                'number':{
                    required:'信息不能为空',
                    minlength: '密码必须6位数以上',
                    text: Lang.text
                }
                //'label_date':{
                //    required: Lang.date_invalid
                //},
                //'label_time':{
                //    time: Lang.time_invalid
                //}
            }
        });
    };

    var show_data = function(name){
        var expire = name + '_expires';
        if(Lang.tweet_data[expire] != null) {
            var time = Goobird.dateToUnix(Lang.tweet_data[expire]);
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
        init : function () {
            initt();
            initValidation();
        }
    }
}();

jQuery(function(){
    $select_multi = $(".js-example-basic-multiple");
    $select = $(".js-example-basic");
    $select_multi.select2({
        selectOnClose: false
    });
    $select.select2({
        minimumResultsForSearch: Infinity
    });
    $('.datepicker').datepicker({
        startDate: new Date().toLocaleDateString()
    });
    $('input[name=_timezone]').val((new Date()).getTimezoneOffset());
    editTweet.init();

    $(document).on('change','#label_name',function(){
        if(this.value == ''){
            $('#label').addClass('hidden');
        }else{
            $('#label').removeClass('hidden');
        }
    });
});