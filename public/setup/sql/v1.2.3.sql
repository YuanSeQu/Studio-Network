
DROP TABLE IF EXISTS `rrz_goods_brand`;
CREATE TABLE `rrz_goods_brand`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '品牌表',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '品牌名称',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '品牌logo',
  `desc` text NULL COMMENT '备注介绍',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '品牌地址',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 100 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM COMMENT = '产品品牌' ROW_FORMAT = Dynamic;


ALTER TABLE `rrz_goods`
ADD COLUMN `brand_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '产品品牌id' AFTER `cat_id`;

ALTER TABLE `rrz_goods`
ADD COLUMN `seo_title` varchar(255) NULL DEFAULT '' COMMENT 'SEO标题' AFTER `ifpub`,
ADD COLUMN `seo_description` mediumtext NULL COMMENT '分类描述' AFTER `seo_title`,
ADD COLUMN `seo_keywords` varchar(200) NULL DEFAULT '' COMMENT '搜索关键词' AFTER `seo_description`;

ALTER TABLE `rrz_articles`
ADD COLUMN `source` varchar(100) NOT NULL DEFAULT '' COMMENT '来源' AFTER `sort`,
ADD COLUMN `source_url` varchar(255) NOT NULL DEFAULT '' COMMENT '来源URL' AFTER `source`;