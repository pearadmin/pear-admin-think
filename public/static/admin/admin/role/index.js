layui.use(['table', 'form', 'jquery'], function() {
    let table = layui.table;
    let form = layui.form;
    let $ = layui.jquery;
    let cols = [
        [{
                field: 'id',
                 title: 'ID', 
                 sort: true, 
                 align: 'center',
                 unresize: true,
                 width: 80
            },{
                field: 'name',
                title: '角色名称',
                unresize: true,
                align: 'center'
            }, {
                field: 'desc',
                title: '描述',
                unresize: true,
                align: 'center',
            }, 
            {
                field: 'create_time',
                title: '创建时间',
                unresize: true,
                align: 'center'
            }, 
            {
                field: 'update_time',
                title: '更新时间',
                unresize: true,
                align: 'center'
            }, 
            {
                title: '操作',
                toolbar: '#options',
                align: 'center',
                unresize: true,
                width: 150
            }
        ]
    ]

    table.render({
        elem: '#dataTable',
        url: 'index',
        page: true,
        cols: cols,
        skin: 'line',
        toolbar: '#toolbar',
        defaultToolbar: [{
            layEvent: 'refresh',
            icon: 'layui-icon-refresh',
        }, 'filter', 'print', 'exports']
    });

    table.on('tool(dataTable)', function(obj) {
        if (obj.event === 'remove') {
            window.remove(obj);
        } else if (obj.event === 'edit') {
            window.edit(obj);
        }else if (obj.event === 'permission') {
            window.permission(obj);
        }
    });

    table.on('toolbar(dataTable)', function(obj) {
        if (obj.event === 'add') {
            window.add();
        } else if (obj.event === 'refresh') {
            window.refresh();
        }else if (obj.event === 'recycle') {
            window.recycle(obj);
        }
    });
    if (typeof width !== 'number' || width === 0) {
        width = $(window).width() * 0.8;
    }
    if (typeof height !== 'number' || height === 0) {
        height = $(window).height() - 20;
    }
    window.add = function() {
        layer.open({
            type: 2,
            title: '新增角色',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'add'
        });
    }

    window.edit = function(obj) {
        layer.open({
            type: 2,
            title: '修改角色',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'edit?id='+obj.data['id']
        });
    }

    window.permission = function(obj) {
        layer.open({
            type: 2,
            title: '分配直接权限',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'permission?id='+obj.data['id']
        });
    }

    window.remove = function(obj) {
        layer.confirm('确定要删除该角色', {
            icon: 3,
            title: '提示'
        }, function(index) {
            layer.close(index);
            let loading = layer.load();
            $.ajax({
                url:'del',
                data:{id:obj.data['id']},
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
                            obj.del();
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

    window.recycle = function() {
        layer.open({
            type: 2,
            title: '回收站',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'recycle',
            cancel: function () {
                table.reload('dataTable');
            }
        });
    }

    window.refresh = function() {
        table.reload('dataTable');
    }
})