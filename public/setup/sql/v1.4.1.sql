
ALTER TABLE `rrz_articles` ADD COLUMN `deltime` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间';
ALTER TABLE `rrz_articles` ADD INDEX `idx_deltime`(`deltime`);

ALTER TABLE `rrz_article_nodes` ADD COLUMN `deltime` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间';
ALTER TABLE `rrz_article_nodes` ADD INDEX `idx_deltime`(`deltime`);

ALTER TABLE `rrz_goods` ADD COLUMN `deltime` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间';
ALTER TABLE `rrz_goods` ADD INDEX `idx_deltime`(`deltime`);

ALTER TABLE `rrz_goods_cat` ADD COLUMN `deltime` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间';
ALTER TABLE `rrz_goods_cat` ADD INDEX `idx_deltime`(`deltime`);

ALTER TABLE `rrz_goods` ADD COLUMN `pubtime` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '发布时间';
UPDATE `rrz_goods` SET pubtime = addtime;