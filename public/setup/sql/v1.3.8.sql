DROP TABLE IF EXISTS `rrz_tag`;
CREATE TABLE `rrz_tag`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签标题',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '标签类型（1：文章，2：产品）',
  `fgcolor` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签字体颜色',
  `bgcolor` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签背景颜色',
  `seo_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'SEO描述',
  `is_common` tinyint(1) NULL DEFAULT 0 COMMENT '是否常用标签，0=否，1=是',
  `view_count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '点击',
  `total` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '文档数',
  `weekcc` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '周统计',
  `monthcc` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '月统计',
  `weekup` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '每周更新',
  `monthup` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '每月更新',
  `tmpl_path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '模板',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_title`(`title`) USING BTREE,
  INDEX `idx_type`(`type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '标签表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `rrz_tag_rel`;
CREATE TABLE `rrz_tag_rel`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '表id',
  `tag_id` int(10) UNSIGNED NOT NULL COMMENT '标签ID',
  `rel_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '对象ID',
  `tag_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '标签类型（1：文章，2：产品）',
  `type_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '对象分类ID',
  `tag_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签',
  `last_modify` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_tag_type`(`tag_type`) USING BTREE,
  INDEX `idx_tag_id`(`tag_id`) USING BTREE,
  INDEX `idx_rel_id`(`rel_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '标签关联表' ROW_FORMAT = Dynamic;