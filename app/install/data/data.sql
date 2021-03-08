SET NAMES utf8;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `{{$pk}}admin_admin`;
CREATE TABLE `{{$pk}}admin_admin` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名，登陆使用',
  `password` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户密码',
  `nickname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户昵称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态：1正常,2禁用 默认1',
  `token` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'token',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理表';

DROP TABLE IF EXISTS `{{$pk}}admin_admin_role`;
CREATE TABLE `{{$pk}}admin_admin_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理-角色中间表';

DROP TABLE IF EXISTS `{{$pk}}admin_admin_log`;
CREATE TABLE `{{$pk}}admin_admin_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) DEFAULT NULL COMMENT '管理员ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作页面',
  `desc` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '日志内容',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT '操作IP',
  `user_agent` text NOT NULL COMMENT 'User-Agent',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理员日志';

DROP TABLE IF EXISTS `{{$pk}}admin_admin_permission`;
CREATE TABLE `{{$pk}}admin_admin_permission` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `permission_id` int(11) DEFAULT NULL COMMENT '权限ID',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理-权限中间表';

DROP TABLE IF EXISTS `{{$pk}}admin_permission`;
CREATE TABLE `{{$pk}}admin_permission` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(50) DEFAULT NULL COMMENT '名称',
  `href` varchar(50) NOT NULL COMMENT '地址',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `sort` tinyint(4) NOT NULL DEFAULT '99' COMMENT '排序',
  `type` tinyint(1) DEFAULT '1' COMMENT '菜单',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
   PRIMARY KEY (`id`),
   KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 COMMENT='权限表';

INSERT INTO `{{$pk}}admin_permission` (`id`, `pid`, `title`, `href`, `icon`, `sort`, `type`, `status`) VALUES
(1, 0, '后台权限', '', 'layui-icon layui-icon-username', 2, 0, 1),
(2, 1, '管理员', '/admin.admin/index', '', 1, 1, 1),
(3, 1, '角色管理', '/admin.role/index', '', 99, 1, 1),
(4, 1, '菜单权限', '/admin.permission/index', '', 99, 1, 1),
(5, 0, '系统管理', '', 'layui-icon layui-icon-set', 3, 0, 1),
(6, 5, '后台日志', '/admin.admin/log', '', 2, 1, 1),
(7, 5, '系统设置', '/admin.config/index', '', 1, 1, 1),
(8, 5, '图片管理', '/admin.photo/index', '', 2, 1, 1);

DROP TABLE IF EXISTS `{{$pk}}admin_role`;
CREATE TABLE `{{$pk}}admin_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '名称',
  `desc` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '描述',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 COMMENT='角色表';

DROP TABLE IF EXISTS `{{$pk}}admin_role_permission`;
CREATE TABLE `{{$pk}}admin_role_permission` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  `permission_id` int(11) DEFAULT NULL COMMENT '权限ID',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='角色-权限中间表';

INSERT INTO `{{$pk}}admin_role` (`id`, `name`, `desc`, `create_time`, `update_time`, `delete_time`) VALUES
(1, '超级管理员', '拥有所有管理权限', '2020-09-01 11:01:34', '2020-09-01 11:01:34', NULL);

DROP TABLE IF EXISTS `{{$pk}}admin_config`;
CREATE TABLE `{{$pk}}admin_config` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `key` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '键',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '名称',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '值',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 COMMENT='系统设置';

INSERT INTO `{{$pk}}admin_config` (`id`, `key`, `name`, `value`) VALUES
(1, 'web', '网站设置', '{"title":"Pear Admin Thinkphp","key":"Pear Admin Thinkphp","desc":"Pear Admin Thinkphp","tel":"17777777777","qq":"123456","mail":"123456@qq.com","addr":"\\u4e2d\\u56fd","logo":"","bg":"","login_captcha":"1"}'),
(2, 'email', '邮箱设置', '{"smtp-user":"123456@qq.com","smtp-pass":"234","smtp-port":"465","smtp-host":"smtp.qq.com"}'),
(3, 'file', '文件设置', '{"file-type":"1","file-endpoint":"img.pear.cn","file-OssName":"pear-img","file-accessKeyId":"123123s","file-accessKeySecret":"asdfasdfasdfsadfasdf"}');

DROP TABLE IF EXISTS `{{$pk}}admin_photo`;
CREATE TABLE `{{$pk}}admin_photo` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL COMMENT '文件名称',
  `href` varchar(255) DEFAULT NULL COMMENT '文件路径',
  `mime` varchar(50) NOT NULL COMMENT 'mime类型',
  `size` varchar(30) NOT NULL COMMENT '大小',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1本地2阿里云',
  `ext` varchar(10) DEFAULT NULL COMMENT '文件后缀',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='图片表';
