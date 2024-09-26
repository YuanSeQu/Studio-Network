ALTER TABLE `rrz_goods`
ADD COLUMN `ifpub` enum('true','false') NOT NULL DEFAULT 'true' COMMENT '上架';