
ALTER TABLE `rrz_article_nodes`
ADD COLUMN `view_route` varchar(255) NOT NULL DEFAULT '{typedir}/{aid}.html' COMMENT '详情页路由' AFTER `tmpl_view`,
ADD COLUMN `list_route` varchar(255) NOT NULL DEFAULT '{typedir}/list_{tid}_{page}.html' COMMENT '列表页路由' AFTER `view_route`;

ALTER TABLE `rrz_articles`
ADD COLUMN `subtitle` varchar(255) NULL DEFAULT '' COMMENT '副标题' AFTER `title`;

ALTER TABLE `rrz_goods_cat`
ADD COLUMN `view_route` varchar(255) NOT NULL DEFAULT '{typedir}/{aid}.html' COMMENT '详情页路由' AFTER `tmpl_view`,
ADD COLUMN `list_route` varchar(255) NOT NULL DEFAULT '{typedir}/list_{tid}_{page}.html' COMMENT '列表页路由' AFTER `view_route`,
ADD COLUMN `ifpub` enum('true','false') NOT NULL DEFAULT 'true' COMMENT '发布' AFTER `attrs`;


ALTER TABLE `rrz_goods`
ADD COLUMN `subtitle` varchar(255) NULL DEFAULT '' COMMENT '副标题' AFTER `name`;

ALTER TABLE `rrz_goods`
ADD INDEX `idx_brand_id`(`brand_id`);