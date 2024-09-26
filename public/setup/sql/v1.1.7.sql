
DROP TABLE IF EXISTS `rrz_goods_attr`;
CREATE TABLE `rrz_goods_attr`  (
  `id` bigint(18) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品属性id自增',
  `goods_id` bigint(18) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `attr_value` text NULL COMMENT '属性值',
  `attr_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '属性id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `attr_id`(`attr_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 COMMENT = '产品扩展属性关联表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `rrz_goods_attribute`;
CREATE TABLE `rrz_goods_attribute`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '属性名称',
  `is_filter` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否应用于条件筛选',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型（0 单行文本框 1下拉式列表 2多行文本框）',
  `values` text NULL COMMENT '属性值集合',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '属性排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 COMMENT = '产品扩展属性' ROW_FORMAT = Dynamic;


#新增表字段
ALTER TABLE `rrz_goods_cat` 
ADD COLUMN `attrs` longtext NULL COMMENT '参数列表' AFTER `seo_keywords`;

ALTER TABLE `rrz_search_keywords` 
ADD COLUMN `type` varchar(50) NULL DEFAULT 'article' COMMENT '搜索类型' AFTER `keywords`;