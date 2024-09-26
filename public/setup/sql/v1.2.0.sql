ALTER TABLE `rrz_site_menus`
ADD COLUMN `en_title` varchar(100) NOT NULL DEFAULT '' COMMENT '英文标题' AFTER `title`;

ALTER TABLE `rrz_article_nodes`
ADD COLUMN `en_title` varchar(255) NOT NULL DEFAULT '' COMMENT '栏目英文名称' AFTER `name`;

ALTER TABLE `rrz_goods_cat`
ADD COLUMN `en_title` varchar(255) NOT NULL DEFAULT '' COMMENT '分类英文名称' AFTER `name`;