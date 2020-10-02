layui.use(['form','jquery'],function(){
    let layform = layui.form;
    let $ = layui.jquery;
    layform.on('checkbox', function (data) {
        var check = data.elem.checked;//是否选中
        var checkId = data.elem.id;//当前操作的选项框
        if (check) {
            //选中
            var ids = checkId.split("-");
            if (ids.length == 3) {
                //第三极菜单
                //第三极菜单选中,则他的上级选中
                $("#" + (ids[0] + '-' + ids[1])).prop("checked", true);
                $("#" + (ids[0])).prop("checked", true);
            } else if (ids.length == 2) {
                //第二季菜单
                $("#" + (ids[0])).prop("checked", true);
                $("input[id*=" + ids[0] + '-' + ids[1] + "]").each(function (i, ele) {
                    $(ele).prop("checked", true);
                });
            } else {
                //第一季菜单不需要做处理
                $("input[id*=" + ids[0] + "-]").each(function (i, ele) {
                    $(ele).prop("checked", true);
                });
            }
        } else {
            //取消选中
            var ids = checkId.split("-");
            if (ids.length == 2) {
                //第二极菜单
                $("input[id*=" + ids[0] + '-' + ids[1] + "]").each(function (i, ele) {
                    $(ele).prop("checked", false);
                });
            } else if (ids.length == 1) {
                $("input[id*=" + ids[0] + "-]").each(function (i, ele) {
                    $(ele).prop("checked", false);
                });
            }
        }
        layform.render();
    });

    layform.on('submit(save)', function(data){
        $.ajax({
            data:data.field,
            type:'post',
            success:function(res){
                //判断有没有权限
                if(res && res.code==999){
                    layer.msg(res.msg, {
                        icon: 5,
                        time: 2000, 
                    })
                    return false;
                }else if(res.code==200){
                    layer.msg(res.msg,{icon:1,time:1000},function(){
                        parent.layer.close(parent.layer.getFrameIndex(window.name));//关闭当前页
                        parent.layui.table.reload("user-table");
                    });
                }else{
                    layer.msg(res.msg,{icon:2,time:1000});
                }
            }
        })
        return false;
    });
})