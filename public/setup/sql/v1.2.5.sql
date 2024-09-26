DROP TABLE IF EXISTS `rrz_plugin`;
CREATE TABLE `rrz_plugin`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '编码',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '版本号',
  `author` varchar(30) NOT NULL DEFAULT '' COMMENT '作者',
  `isadmin` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否存在后台（0否1是）',
  `ishome` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否存在前台（0否1是）',
  `isload` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否自动加载（0手动加载、1自动加载）',
  `loadtemp` text NULL COMMENT '自动加载在指定模板，空白默认全部',
  `config` text NULL COMMENT '额外参数',
  `data` text NULL COMMENT '配置存储数据，简单插件可以不创建表',
  `saflag` varchar(500) NULL DEFAULT NULL COMMENT '标示内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0未安装、1启用、2禁用）',
  `addtime` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM COMMENT = '插件表' ROW_FORMAT = Dynamic;