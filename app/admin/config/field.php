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
    'disable' => [
        'url', 'title', 'name', 'img', 'seo_title', 'seo_description',
        'seo_keywords', 'node_name', 'uptime', 'pubtime', 'brief',
        'author', 'class', 'type',
    ],//禁用的字段不允许用户定义
    'type' => [
        'text' => ['name' => 'text', 'title' => '单行文本', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'multitext' => ['name' => 'multitext', 'title' => '多行文本', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'radio' => ['name' => 'radio', 'title' => '单选项', 'is_option' => 1, 'is_dfvalue' => 1, 'is_filter' => 1,],
        'checkbox' => ['name' => 'checkbox', 'title' => '多选项', 'is_option' => 1, 'is_dfvalue' => 1, 'is_filter' => 1,],
        'select' => ['name' => 'select', 'title' => '下拉框', 'is_option' => 1, 'is_dfvalue' => 1, 'is_filter' => 1,],
        'int' => ['name' => 'int', 'title' => '整数类型', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'float' => ['name' => 'float', 'title' => '小数类型', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'decimal' => ['name' => 'decimal', 'title' => '金额类型', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'html' => ['name' => 'html', 'title' => 'HTML文本', 'is_option' => 0, 'is_dfvalue' => 1, 'is_filter' => 0,],
        'img' => ['name' => 'img', 'title' => '单张图', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'imgs' => ['name' => 'imgs', 'title' => '多张图', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'datetime' => ['name' => 'datetime', 'title' => '日期和时间', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'switch' => ['name' => 'switch', 'title' => '开关', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'video' => ['name' => 'video', 'title' => '上传视频', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'audio' => ['name' => 'audio', 'title' => '上传音频', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
        'attachment' => ['name' => 'attachment', 'title' => '上传附件', 'is_option' => 0, 'is_dfvalue' => 0, 'is_filter' => 0,],
    ],
];