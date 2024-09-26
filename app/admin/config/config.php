<?php
/**
 * 人人站CMS
 * ============================================================================
 * 版权所有 2015-2030 山东康程信息科技有限公司，并保留所有权利。
 * 网站地址: http://www.rrzcms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 */

return [
    //脚本样式版本号
    'version' => '20220406',
    //应用列表
    'apps' => ['admin', 'home', 'install'],
    //文档SEO描述截取长度，一个字符表示一个汉字或字母
    'seo_description_length' => 125,
    //系统版本
    'sys_version' => 'v1.4.1',

    // 数据管理
    'DATA_BACKUP_PATH' => app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'sqldata', //数据库备份根路径
    'DATA_BACKUP_PART_SIZE' => 52428800, //数据库备份卷大小 50M
    'DATA_BACKUP_COMPRESS' => 0, //数据库备份文件是否启用压缩
    'DATA_BACKUP_COMPRESS_LEVEL' => 9, //数据库备份文件压缩级别
];