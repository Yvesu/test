/**
 * Created by mabiao on 2016/3/24.
 */

var admin_department = new Vue({
    el:"#add-admin-department-vue",
    data:{
        department:""
    },
    watch:{
        'department': function (val, oldVal) {
            if("" == val){
                admin_position.$set('positions','');
            }else{
                $.get("/admin/department/" + val, function(positions){
                    admin_position.$set('positions',positions)
                });
            }
        }
    }
});
var admin_position = new Vue({
    el:"#add-admin-position-vue",
    data:{
        positions: []
    },
    watch:{
        'positions':function(val,oldVal){
            if(null != EDIT_ADMIN_POSITION_ID){
                $('#edit-admin-position option[value='+EDIT_ADMIN_POSITION_ID+']').attr("selected","selected");
                EDIT_ADMIN_POSITION_ID = null;
            }
        }
    }
});


