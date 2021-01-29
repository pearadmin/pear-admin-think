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
        toolbar: '#dataTable',
        defaultToolbar: [{
            layEvent: 'refresh',
            icon: 'layui-icon-refresh',
        }, 'filter', 'print', 'exports']
    });
    
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
        }
    });
})