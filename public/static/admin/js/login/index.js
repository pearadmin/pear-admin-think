layui.use(['form', 'element', 'jquery', 'button'], function() {
    var form = layui.form;
    var element = layui.element;
    var button = layui.button;
    var $ = layui.jquery;
    // 登 录 提 交
    form.on('submit(login-submit)', function(data) {
        button.load({
            elem: '.login',
            time: 100,
            done: function() {
                $.ajax({
                    data: data.field,
                    type:"POST",
                    dataType:"json",
                    success: function(res) {
                        if (res.code=='0') {
                            layer.msg(res.msg, {
                                icon: 2,
                            });
                            initCode();
                        } else {
                            layer.msg(res.msg, {
                                icon: 1,
                            });
                            setTimeout(function() {
                                location.href = '';
                            }, 333)
                        }
                    },
                    error: function() {
                        layer.msg('系统异常', {
                            icon: 0,
                        });
                        initCode();
                    }
                });
             return false;
            }
        })
    });
    initCode();
    function initCode() {
        $('#codeimg').attr("src","login/verify?data=" + new Date().getTime());
    }
    $('#codeimg').on('click', function () {
        initCode();
    });
})