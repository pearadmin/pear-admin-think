layui.use(['form', 'jquery'], function() {
    let form = layui.form;
    let $ = layui.jquery;
    form.on('switch(login_captcha)', function(data) {
        if (data.elem.checked) {
            $(data.elem).val('1');
        } else {
            $(data.elem).val('0');
        }
    });
    form.on('submit(configform)', function(data) {
        if (!data.field.login_captcha) {
            data.field.login_captcha = '0';
        }
        $.ajax({
            data:JSON.stringify(data.field),
            dataType:'json',
            contentType:'application/json',
            type:'post',
            success:function(res){
                layer.msg(res.msg,{icon:1,time:1000},function(){});
            }
        })
        return false;
    });
    form.on('radio(type)', function(data){
        if(data.value==2){
            $("#oss").show();
        }else{
            $("#oss").hide();
        }
    });  
})