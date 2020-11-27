layui.use(['table', 'form', 'code','jquery'], function() {
    let table = layui.table;
    let $ = layui.jquery;
    layui.code(); 
    let cols = [
        [
            {
                title: '数据表',
                field: 'TABLE_NAME'
            },{
                title: '操作',
                toolbar: '#options',
                align: 'center',
                width: 200
            }
        ]
    ]

    $(".table-node").click(function(){
        $(".empty").remove();
        table.render({
            elem: '#dataTable',
            url: '?name='+$(this).find("span").text(),
            page: false,
            cols: cols,
            skin: 'line',
            toolbar: false,
            defaultToolbar: false
        });
    })

    table.on('tool(dataTable)', function(obj) {
        if (obj.event === 'crud') {
            layer.open({
                type: 2,
                maxmin: true,
                title: '生成',
                shade: 0.1,
                area: [width + 'px', height + 'px'],
                content:'crud?name='+obj.data['TABLE_NAME']
            });
        }
    });
    
    if (typeof width !== 'number' || width === 0) {
        width = $(window).width() * 0.8;
    }
    if (typeof height !== 'number' || height === 0) {
        height = $(window).height() - 20;
    }
    $("#addMulti").click(function(){
        layer.open({
            type: 2,
            maxmin: true,
            title: '新增多级',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'addMulti'
        });
    })

    $("#addBase").click(function(){
        layer.open({
            type: 2,
            maxmin: true,
            title: '新增基础表',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'addBase'
        });
    })

    delMulti = function(e) {
        layer.confirm('确定要删除该多级地址吗?', {
            icon: 3,
            title: '提示'
        }, function(index) {
            layer.close(index);
            let loading = layer.load();
            $.ajax({
                url:'delMulti',
                data:{name:e},
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
        });
    }
})