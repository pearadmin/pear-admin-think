layui.use(['form', 'layer', 'jquery', 'element'], function () {
    var $ = layui.jquery
             , layer = layui.layer
             , element = layui.element
             , form = layui.form;
         form.on('submit(formLogin)', function (data) {
             layer.load();
             $.ajax({
                 type: "POST",
                 data: data.field,
                 success: function (res) {
                     layer.closeAll('loading');
                     //验证通过
                     if (res.code==200){
                         layer.msg(res.msg,{icon:1,time:1500},function () {
                              location.href = MODULE_PATH?MODULE_PATH:'/';
                         })
                     } else {
                         layer.msg(res.msg,{icon:2,time:1500},function () {
                           initCode();
                         })
                     }
                 }
             });
             return false;
     });
     initCode();
     function initCode() {
         $('#codeimg').attr("src",MODULE_PATH+"/login/verify?data=" + new Date().getTime());
     }
     $('#codeimg').on('click', function () {
         initCode();
     });
 })