layui.use(['table', 'form', 'code','jquery'], function() {
    let table = layui.table;
    let $ = layui.jquery;
    layui.code(); 
    let cols = [
        [
            {
                title: '名字',
                field: 'COLUMN_NAME'
            },{
                title: '注释',
                field: 'COLUMN_COMMENT'
            },{
                title: '类型',
                field: 'DATA_TYPE'
            },{
                title: '是否为空',
                field: 'IS_NULLABLE'
            },
        ]
    ]

    $(".node").click(function(){
        lists($(this).find("span").text());
    })

    crud = function(e) {
        layer.open({
            type: 2,
            maxmin: true,
            title: '生成',
            shade: 0.1,
            area: screen(),
            content:'crud?name='+e
        });
    }

    del = function(e) {
        layer.confirm('确定要删除吗?', {
            icon: 3,
            title: '提示'
        }, function() {
            layer.confirm('数据表是否删除', {
            icon: 2,
            btn : ['删除','不删除'],
            btn1:function(index){
                layer.close(index);
                let loading = layer.load();
                $.ajax({
                    url:'del',
                    data:{name:e,type:true},
                    dataType: 'json',
                    type: 'POST',
                    success: function(res) {
                        layer.close(loading);
                        if (res.code==200) {
                            layer.msg(res.msg, {
                                icon: 1,
                                time: 1000
                            }, function() {
                                location.reload();
                            });
                        } else {
                            layer.msg(res.msg, {
                                icon: 2,
                                time: 1000
                            });
                        }
                    }
                })
            },
            btn2:function(index){
                layer.close(index);
                let loading = layer.load();
                $.ajax({
                    url:'del',
                    data:{name:e,type:false},
                    dataType: 'json',
                    type: 'POST',
                    success: function(res) {
                        layer.close(loading);
                        if (res.code==200) {
                            layer.msg(res.msg, {
                                icon: 1,
                                time: 1000
                            }, function() {
                                location.reload();
                            });
                        } else {
                            layer.msg(res.msg, {
                                icon: 2,
                                time: 1000
                            });
                        }
                    }
                })
            },
        });
     });
    }

    lists = function(e) {
        $(".empty").remove();
        table.render({
            elem: '#dataTable',
            url: '?name='+e,
            page: false,
            cols: cols,
            skin: 'line',
            toolbar: false,
            defaultToolbar: false
        });
    }
    lists($(".node").find("span").eq(0).text());
    
    //弹出窗设置 自己设置弹出百分比
    function screen() {
        if (typeof width !== 'number' || width === 0) {
          width = $(window).width() * 0.8;
        }
        if (typeof height !== 'number' || height === 0) {
          height = $(window).height() - 20;
        }
        return [width + 'px', height + 'px'];
    }

    $("#addBase").click(function(){
        layer.open({
            type: 2,
            maxmin: true,
            title: '新增基础表',
            shade: 0.1,
            area: screen(),
            content:'addBase'
        });
    })
})