layui.use(['table','form','treetable', 'jquery'],function () {
    let table = layui.table;
    let form = layui.form;
    let treetable = layui.treetable;
    let $ = layui.jquery;
    window.render = function(){
        treetable.render({
            treeColIndex: 0,
            treeSpid: 0,
            treeIdName: 'id',
            treePidName: 'pid',
            skin:'line',
            method:'post',
            treeDefaultClose: true,
            toolbar:'#power-toolbar',
            elem: '#power-table',
            url: 'index',
            page: false,
            cols: [
                [
                {field: 'title', minWidth: 200, title: '权限名称'},
                {field: 'multi', title: '所属多级',unresize: true, align: 'center'},
                {field: 'icon',title: '图标', unresize: true, align: 'center',
                    templet:function (d) {
                        return '<i class="layui-icon '+d.icon+'"></i>';
                    }
                }, 
                {field: 'type', title: '权限类型',templet:'#power-type'},
                {title: '状态', field: 'status',align: 'center',unresize: true,templet: '#status',width: 100},
                {field: 'sort', title: '排序'},
                {title: '操作',templet: '#power-bar', width: 150, align: 'center'}
                ]
            ]
        });
    }
    render();
    table.on('tool(power-table)',function(obj){
        if (obj.event === 'remove') {
            window.remove(obj);
        } else if (obj.event === 'edit') {
            window.edit(obj);
        } 
    })
    if (typeof width !== 'number' || width === 0) {
        width = $(window).width() * 0.8;
    }
    if (typeof height !== 'number' || height === 0) {
        height = $(window).height() - 20;
    }
    table.on('toolbar(power-table)', function(obj){
        if(obj.event === 'add'){
            layer.open({
                type: 2,
                title: '新增菜单',
                shade: 0.1,
                area: [width + 'px', height + 'px'],
                content:'add'
            });
        } else if(obj.event === 'expandAll'){
            treetable.expandAll("#power-table");
        } else if(obj.event === 'foldAll'){
            treetable.foldAll("#power-table");
       }
    });
    window.edit = function(obj){
        layer.open({
            type: 2,
            title: '修改菜单',
            shade: 0.1,
            area: [width + 'px', height + 'px'],
            content:'edit?id='+obj.data['id']
        });
    }
    window.remove = function(obj){
        layer.confirm('确定要删除该菜单', {
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
                            render();
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
    form.on('switch(status)', function(data) {
        var status = data.elem.checked?1:2;
        var id = this.value;
        var load = layer.load();
        $.post('status',{status:status,id:id},function (res) {
            layer.close(load);
            if (res.code==200){
                layer.msg(res.msg,{icon:1,time:1500})
            } else {
                layer.msg(res.msg,{icon:2,time:1500},function () {
                    $(data.elem).prop('checked',!$(data.elem).prop('checked'));
                    form.render()
                })
            }
        })
    });
})