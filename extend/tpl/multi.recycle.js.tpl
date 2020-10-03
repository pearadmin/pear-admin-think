layui.use(['table', 'form', 'jquery','laydate'], function() {
    let table = layui.table;
    let form = layui.form;
    let $ = layui.jquery;
    let laydate = layui.laydate;
    {{$searchs}}
    let cols = [
        [{
            type: 'checkbox'
            },{{$columns}}]
    ];
    table.render({
        elem: '#dataTable',
        url:'recycle',
        page: true,
        cols: cols,
        skin: 'line',
        toolbar: '#toolbar',
        defaultToolbar: [{
            layEvent: 'refresh',
            icon: 'layui-icon-refresh',
        }, 'filter', 'print', 'exports']
    });

    table.on('toolbar(dataTable)', function(obj) {
        if (obj.event === 'batchRemove') {
            window.batchRemove(obj);
        }else if (obj.event === 'renew') {
            window.renew(obj);
        }else if (obj.event === 'refresh') {
            window.refresh();
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

    window.renew = function(obj) {
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
    layer.confirm('确定要恢复该{{$cname}}', {
        icon: 3,
        title: '提示'
    }, function(index) {
        layer.close(index);
        let loading = layer.load();
        $.ajax({
            data:{ids:ids,type:1},
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
                data:{ids:ids,type:2},
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

    window.refresh = function() {
        table.reload('dataTable');
    }
})