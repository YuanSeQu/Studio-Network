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
    'tabs' => [
        ['name' => 'local', 'title' => '本地图片', 'url' => '',],
        ['name' => 'network', 'title' => '网络图片', 'url' => '',],
    ],
    'config' => [
        'video' => ['fileSize' => 4194304, 'fileExt' => 'mp4,webm,ogg', 'fileMime' => 'video/mp4,video/webm,video/ogg',],
        'audio' => ['fileSize' => 4194304, 'fileExt' => 'mp3,ogg,wav', 'fileMime' => 'audio/mpeg,audio/ogg,audio/x-wav',],
        'images' => ['fileSize' => 4194304, 'fileExt' => 'jpg,jpeg,png,bmp,gif,ico', 'fileMime' => 'image/jpeg,image/pjpeg,image/png,image/bmp,image/x-ms-bmp,image/gif,image/x-icon,image/vnd.microsoft.icon',],
        'other' => ['fileSize' => 4194304, 'fileExt' => 'zip,gz,rar,iso,doc,xls,ppt,wps',],
    ],
    'watermark' => [
        'default' => [
            'is_enable' => false,
            'type' => 'text',
            'min_width' => 200,
            'min_height' => 50,
            'text' => '人人站CMS',
            'text_size' => '30',
            'text_color' => '#000000',
            'opacity' => 60,
            'locate' => '9',
        ],
        'font_file' => public_path() . 'static/fonts/aliPuHuiTi-2-Heavy.ttf',
    ],
];