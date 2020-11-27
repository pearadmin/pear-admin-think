layui.use(['admin', 'jquery', 'layer','element'], function() {
    var $ = layui.jquery;
    var layer = layui.layer;
    var layelem = layui.element;
    var admin = layui.admin;
    // 框 架 初 始 化
    admin.render({
        "logo": {
            "title": "Pear Admin",
            "image": "/static/admin/images/logo.png"
        },
        "menu": {
            "data": MODULE_PATH+"admin.index/menu",
            "accordion": true,
            "control": false,
            "select": "0"
        },
        "tab": {
            "muiltTab": true,
            "keepState": true,
            "tabMax": 30,
            "index": {
                "id": "0",
                "href": MODULE_PATH+"admin.index/home",
                "title": "首页"
            }
        },
        "theme": {
            "defaultColor": "2",
            "defaultMenu": "dark-theme",
            "allowCustom": true
        },
        "colors": [{
                "id": "1",
                "color": "#FF5722"
            },
            {
                "id": "2",
                "color": "#5FB878"
            },
            {
                "id": "3",
                "color": "#1E9FFF"
            }, {
                "id": "4",
                "color": "#FFB800"
            }, {
                "id": "5",
                "color": "darkgray"
            }
        ],
        "links": [{
                "icon": "layui-icon layui-icon-website",
                "title": "官方网站",
                "href": "http://www.pearadmin.com"
            },
            {
                "icon": "layui-icon layui-icon-read",
                "title": "开发文档",
                "href": "http://www.pearadmin.com/doc/"
            },
            {
                "icon": "layui-icon layui-icon-fonts-code",
                "title": "开源地址",
                "href": "https://gitee.com/Jmysy/Pear-Admin-Layui"
            },
            {
                "icon": "layui-icon layui-icon-survey",
                "title": "问答社区",
                "href": "http://forum.pearadmin.com/"
            }
        ],
        "other": {
            "keepLoad": 100
        }
    });
    layelem.on('nav(layui_nav_right)', function(elem) {
        if ($(elem).hasClass('logout')) {
            layer.confirm('确定退出登录吗?', function(index) {
                layer.close(index);
                $.ajax({
                    url: MODULE_PATH+'admin.login/logout',
                    type:"POST",
                    dataType:"json",
                    success: function(res) {
                        if (res.code==200) {
                            layer.msg(res.msg, {
                                icon: 1
                            });
                            setTimeout(function() {
                                location.href = MODULE_PATH+'admin.login/index';
                            }, 333)
                        }
                    }
                });
            });
        }else if ($(elem).hasClass('password')) {
            layer.open({
                type: 2,
                maxmin: true,
                title: '修改密码',
                shade: 0.1,
                area: ['300px', '300px'],
                content:MODULE_PATH+'admin.index/pass'
            });
        }else if ($(elem).hasClass('cache')) {
            $.post(MODULE_PATH+'admin.index/cache',
            function(data){
                layer.msg(data.msg, {time: 1500});
                 location.reload()
            });

        }

    });
})