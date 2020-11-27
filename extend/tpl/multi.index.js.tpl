layui.use(['table', 'form', 'jquery','laydate'], function() {
    let table = layui.table;
    let form = layui.form;
    let $ = layui.jquery;
    let laydate = layui.laydate;
    {{$searchs}}
    let cols = [
        [{
            type: 'checkbox'
            },{{$columns}}{
            title: '操作',
            unresize: true,
            align: 'center',
            toolbar: '#options'
        }]
    ];
    table.render({
        elem: '#dataTable',
        url:'index',
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
        }
    });

    table.on('toolbar(dataTable)', function(obj) {
        if (obj.event === 'add') {
            window.add();
        } else if (obj.event === 'refresh') {
            window.refresh();
        } else if (obj.event === 'batchRemove') {
            window.batchRemove(obj);
        }else if (obj.event === 'recycle') {
            window.recycle(obj);
        }
    });

    form.on('submit(query)', function(data) {
        table.reload('dataTable', {
            where: data.field,
            page:{curr: 1}
        })
        {{$searchs}}
        return false;
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
            maxmin: true,
            title: '新增{{$cname}}',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'add'
        });
    }

    window.edit = function(obj) {
        layer.open({
            type: 2,
            maxmin: true,
            title: '修改{{$cname}}',
            shade: 0.1,
            area: [width  + 'px', height + 'px'],
            content:'edit?id='+obj.data['id']
        });
    }

    window.remove = function(obj) {
        layer.confirm('确定要删除该{{$cname}}', {
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

     window.batchRemove = function(obj) {
        let data = table.checkStatus(obj.config.id).data;
        if (data.length === 0) {
            layer.msg("未选中数据", {
                icon: 3,
                time: 1000
            });
            return false;
        }
        var ids = []
        var hasCheck = table.checkStatus('dataTable')
        var hasCheckData = hasCheck.data
        if (hasCheckData.length > 0) {
            $.each(hasCheckData, function (index, element) {
                ids.push(element.id)
            })
        }
        layer.confirm('确定要删除该{{$cname}}', {
            icon: 3,
            title: '提示'
        }, function(index) {
            layer.close(index);
            let loading = layer.load();
            $.ajax({
                url:"delall",
                data:{ids:ids},
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

    window.recycle = function() {
        layer.open({
            type: 2,
            maxmin: true,
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