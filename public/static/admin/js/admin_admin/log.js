layui.use(['table', 'form', 'jquery'], function() {
    let table = layui.table;
    let form = layui.form;
    let $ = layui.jquery;
    let cols = [
        [{
                 field: 'id',
                 title: 'ID',
                 unresize: true,
                 align: 'center',
                 width: 80
            },{
                field: 'uid',
                title: '管理员ID',
                unresize: true,
                align: 'center'
            }, {
                field: 'url',
                title: '操作页面',
                unresize: true,
                align: 'center',
            },  {
                field: 'ip',
                title: '操作IP',
                unresize: true,
                align: 'center',
            }, 
            {
                field: 'desc',
                title: '描述',
                unresize: true,
                align: 'center'
            }, 
            {
                field: 'user_agent',
                title: 'User-Agent',
                unresize: true,
                align: 'center'
            }, 
            {
                field: 'create_time',
                title: '创建时间',
                align: 'center',
                unresize: true,
            }
        ]
    ]

    table.render({
        elem: '#dataTable',
        url:'log',
        page: true,
        cols: cols,
        skin: 'line',
        toolbar: '#toolbar',
        defaultToolbar: [{
            layEvent: 'refresh',
            icon: 'layui-icon-refresh',
        }, 'filter', 'print', 'exports']
    });

    window.batchRemove = function(obj) {
        console.log(1)
        layer.confirm('确定要清空日志嘛？', {
            icon: 3,
            title: '提示'
        }, function(index) {
            layer.close(index);
            let loading = layer.load();
            $.ajax({
                url:"del_log",
                dataType: 'json',
                type: 'POST',
                success: function(res) {
                    layer.close(loading);
                     //判断有没有权限
                     if(res && res.code==999){
                        layer.msg(res.msg, {
                            icon: 5,
                            time: 2000, 
                        })
                        return false;
                    }else if (res.code==200) {
                        layer.msg(res.msg, {
                            icon: 1,
                            time: 1000
                        }, function() {
                            table.reload('dataTable');
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

    form.on('submit(query)', function(data) {
        table.reload('dataTable', {
            where: data.field,
            page:{curr: 1}
        })
        return false;
    });

    table.on('toolbar(dataTable)', function(obj) {
        if (obj.event === 'refresh') {
            table.reload('dataTable');
        }else if (obj.event === 'batchRemove') {
            window.batchRemove(obj);
        }
    });
})