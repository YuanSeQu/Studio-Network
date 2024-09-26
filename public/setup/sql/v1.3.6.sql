ALTER TABLE `rrz_plugin`
ADD COLUMN `sort` int(10) Not NULL DEFAULT '0' COMMENT '执行顺序';

ALTER TABLE `rrz_plugin`
ADD INDEX `idx_code`(`code`),
ADD INDEX `idx_status`(`status`),
ADD INDEX `idx_ishome`(`ishome`),
ADD INDEX `idx_isload`(`isload`);