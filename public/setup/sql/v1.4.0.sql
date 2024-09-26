DROP TABLE IF EXISTS `rrz_users`;
CREATE TABLE `rrz_users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '表id',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `head_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `mobile` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '电子邮件',
  `reg_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '注册时间',
  `last_login` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '最后登录ip',
  `login_count` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '登陆次数',
  `is_activation` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '是否激活，0否，1是。\r\n后台注册默认为1激活。\r\n前台注册时，当会员功能设置选择后台审核，需后台激活才可以登陆。',
  `register_place` tinyint(1) UNSIGNED NULL DEFAULT 2 COMMENT '注册位置。后台注册不受注册验证影响，1为后台注册，2为前台注册。默认为2。',
  `is_lock` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否被锁定冻结',
  `is_del` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '伪删除，1=是，0=否',
  `update_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `rrz_users_attr`;
CREATE TABLE `rrz_users_attr`  (
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '会员id',
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邮箱地址',
  `guige` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '梭梭树',
  `xingbie` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '性别',
  `danxuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '单选项',
  `duohang` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '多行文本',
  `xialakuang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '下拉框',
  `danghangwenben` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '单行文本',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员扩展信息表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `rrz_users_attribute`;
CREATE TABLE `rrz_users_attribute`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '表id',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `dtype` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '字段类型',
  `dfvalue` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '默认值',
  `is_system` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否为系统属性，系统属性不可删除，1为是，0为否，默认0。',
  `is_hidden` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否禁用属性，1为是，0为否',
  `is_required` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否为必填属性，1为是，0为否，默认0。',
  `is_reg` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '是否为注册表单，1为是，0为否',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '会员属性表' ROW_FORMAT = Dynamic;


INSERT INTO `rrz_users_attribute` VALUES (1, '手机号码', 'mobile', 'mobile', '', 1, 0, 0, 0, 1, 1648082968, 0);
INSERT INTO `rrz_users_attribute` VALUES (2, '邮箱地址', 'email', 'email', '', 1, 0, 0, 0, 2, 1648082968, 0);


ALTER TABLE `rrz_articles` ADD COLUMN `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员id';
ALTER TABLE `rrz_articles` ADD COLUMN `admin_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id';

ALTER TABLE `rrz_articles` ADD COLUMN `is_jump` tinyint(1) NULL DEFAULT 0 COMMENT '跳转链接（0=否，1=是）' AFTER `is_recom`;
ALTER TABLE `rrz_articles` ADD COLUMN `jump_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '外链跳转' AFTER `is_jump`;

ALTER TABLE `rrz_goods` ADD COLUMN `is_jump` tinyint(1) NULL DEFAULT 0 COMMENT '跳转链接（0=否，1=是）' AFTER `is_news`;
ALTER TABLE `rrz_goods` ADD COLUMN `jump_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '外链跳转' AFTER `is_jump`;

ALTER TABLE `rrz_goods` ADD COLUMN `admin_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id';