
<div align="center">
<br/>
<br/>
<img src="/public/static/admin/images/logo.png" width="90px" style="margin-top:30px;"/>
  <h1 align="center">
    Pear Admin Think
  </h1>

  [预 览](http://pear.jianla.cn/admin.php)   |   [官 网](http://www.pearadmin.com/)   |   [群聊](https://jq.qq.com/?_wv=1027&k=5OdSmve)   |   [社区](http://forum.pearadmin.com/)


</div>

<p align="center">
    <a href="#">
        <img src="https://img.shields.io/badge/Pear Admin Layui-3.1.0+-green.svg" alt="Pear Admin Layui Version">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/JQuery-2.0+-green.svg" alt="Jquery Version">
    </a>
      <a href="#">
        <img src="https://img.shields.io/badge/Layui-2.5.6+-green.svg" alt="Layui Version">
    </a>
</p>

<div align="center">
  <img  width="92%" style="border-radius:10px;margin-top:20px;margin-bottom:20px;box-shadow: 2px 0 6px gray;" src="https://images.gitee.com/uploads/images/2020/1019/104805_042b888c_4835367.png" />
</div>

#### 项目简介
>Pear Admin Think 基于 thinkphp6 的快速开发平台，通过简单的代码生成功能，即可快速构建你的功能业务，努力成为最顺手的轮子。

#### 演示站信息
* http://pear.jianla.cn/admin.php 账户：test 密码：123456

#### 环境要求
* PHP >= 7.1.0
* Mysql >= 5.7.0 (需支持innodb引擎)
* Apache 或 Nginx
* 需要支持PATH_INFO

#### 安装配置
* git clone https://gitee.com/pear-admin/Pear-Admin-Think
* 更新包composer update
* 将网站入口部署至public目录下面
* 修改伪静态配置, 请参考下方伪静态设置。
* 运行网站地址, 会自动进入安装界面, 请根据提示进行设置, 然后点击安装。
* 安装完成后会自动生成安装锁public/install.lock, 如需重新安装, 删掉该文件即可

#### 代码一键生成CRUD方法
>env APP_DEBUG = true

* 第一步.创建多级
* 第二步.根据多级创建对应前缀数据表。
* 第三步.选择多级数据表生成。
* 建议定义软删除delete_time，自动生成回收站功能。如不需要可自行删除。

#### 页面展示
![输入图片说明](https://images.gitee.com/uploads/images/2021/0108/151730_3a321dbc_1302383.png "1.png")
![输入图片说明](https://images.gitee.com/uploads/images/2021/0108/151737_1d98a6db_1302383.png "2.png")
![输入图片说明](https://images.gitee.com/uploads/images/2021/0108/151744_a26b9301_1302383.png "4.png")

本框架为开源框架，Apache 开源协议，支持商用，学习
