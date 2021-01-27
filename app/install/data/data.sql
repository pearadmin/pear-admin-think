SET NAMES utf8;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `admin_admin`;
CREATE TABLE `admin_admin` (
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

DROP TABLE IF EXISTS `admin_admin_role`;
CREATE TABLE `admin_admin_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理-角色中间表';

DROP TABLE IF EXISTS `admin_admin_log`;
CREATE TABLE `admin_admin_log` (
   `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) DEFAULT NULL COMMENT '管理员ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '操作页面',
  `desc` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '日志内容',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT '操作IP',
  `user_agent` varchar(300) NOT NULL COMMENT 'User-Agent',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='管理员日志';

DROP TABLE IF EXISTS `admin_permission`;
CREATE TABLE `admin_permission` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` char(50) DEFAULT NULL COMMENT '名称',
  `href` char(50) NOT NULL COMMENT '地址',
  `icon` char(50) DEFAULT NULL COMMENT '图标',
  `sort` tinyint(4) NOT NULL DEFAULT '99' COMMENT '排序',
  `type` tinyint(1) DEFAULT '1' COMMENT '菜单',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
   PRIMARY KEY (`id`),
   KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 COMMENT='权限表';

INSERT INTO `admin_permission` (`id`, `pid`, `title`, `href`, `icon`, `sort`, `type`, `status`) VALUES
(1, 0, '后台权限', '', 'layui-icon layui-icon-username', 2, 0, 1),
(2, 1, '管理员', '/admin_admin/index', '', 1, 1, 1),
(3, 1, '角色管理', '/admin_role/index', '', 99, 1, 1),
(4, 1, '菜单权限', '/admin_permission/index', '', 99, 1, 1),
(5, 0, '系统管理', '', 'layui-icon layui-icon-set', 3, 0, 1),
(6, 5, '后台日志', '/admin_config/log', '', 2, 1, 1),
(7, 5, '系统设置', '/admin_config/index', '', 1, 1, 1),
(8, 5, '图片管理', '/admin_config/photo', '', 2, 1, 1),
(9, 0, '内容管理', '/', 'layui-icon layui-icon-file-b', 4, 0, 1),
(10, 9, '新闻列表', '/home_news/index', '', 10, 1, 1),
(11, 10, '添加新闻', '/home_news/add','', 10, 1, 1),
(12, 10, '编辑新闻', '/home_news/edit', '', 10, 1, 1),
(13, 10, '删除新闻', '/home_news/del', '', 10, 1, 1),
(14, 10, '选中删除新闻', '/home_news/delall', '', 10, 1, 1),
(15, 10, '回收站新闻', '/home_news/recycle', '', 10, 1, 1);

DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '名称',
  `desc` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '描述',
  `permissions` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '权限ID',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 COMMENT='角色表';

INSERT INTO `admin_role` (`id`, `name`, `desc`, `create_time`, `update_time`, `delete_time`) VALUES
(1, '超级管理员', '拥有所有管理权限', '2020-09-01 11:01:34', '2020-09-01 11:01:34', NULL);

DROP TABLE IF EXISTS `admin_config`;
CREATE TABLE `admin_config` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` char(50) NOT NULL COMMENT '名称',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '值',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 COMMENT='系统设置';

INSERT INTO `admin_config` (`id`, `name`, `value`) VALUES
(1, 'login_captcha', '0'),
(2, 'smtp-user', '123456@qq.com'),
(3, 'smtp-pass', '234'),
(4, 'smtp-port', '465'),
(5, 'smtp-host', 'smtp.qq.com'),
(6, 'file-type', '1'),
(7, 'file-endpoint', 'img.pear.cn'),
(8, 'file-OssName', 'pear-img'),
(9, 'file-accessKeyId', '123123s'),
(10, 'file-accessKeySecret', 'asdfasdfasdfsadfasdf'),
(11, 'title', 'Pear Admin Thinkphp'),
(12, 'key', 'Pear Admin Thinkphp'),
(13, 'desc', 'Pear Admin Thinkphp'),
(14, 'tel', '17777777777'),
(15, 'qq', '123456'),
(16, 'mail', '123456@qq.com'),
(17, 'addr', '中国');

DROP TABLE IF EXISTS `admin_photo`;
CREATE TABLE `admin_photo` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` char(50) NOT NULL COMMENT '文件名称',
  `href` varchar(255) DEFAULT NULL COMMENT '文件路径',
  `mime` char(50) NOT NULL COMMENT 'mime类型',
  `size` char(30) NOT NULL COMMENT '大小',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1本地2阿里云',
  `ext` char(10) DEFAULT NULL COMMENT '文件后缀',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='图片表';

DROP TABLE IF EXISTS `home_news`;
CREATE TABLE `home_news` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `img` varchar(255) NOT NULL COMMENT '缩略图',
  `desc` text COMMENT '内容',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='新闻';
