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

/**
 * 菜单配置
 */

return [
    [
        'title' => '站点管理',
        'icon' => 'layui-icon-website',
        'action' => '',
        'visible' => 1,
        'list' => [
            [
                'title' => '导航菜单',
                'icon' => 'layui-icon-cols',
                'action' => 'Site/menus',
                'visible' => 1,
            ],
            [
                'title' => '站点设置',
                'icon' => 'layui-icon-set-fill',
                'action' => 'Site/setting',
                'visible' => 1,
                'list' => [

                ],
            ],
            [
                'title' => 'SEO设置',
                'icon' => 'layui-icon-release',
                'action' => 'Seo/index',
                'visible' => 1,
                'list' => [
                    [
                        'title' => 'SEO设置',
                        'action' => 'Seo/index',
                        'visible' => 2,
                    ],
                    [
                        'title' => 'Sitemap',
                        'action' => 'Seo/sitemap',
                        'visible' => 2,
                    ],
                    [
                        'title' => '友情链接',
                        'action' => 'Seo/links',
                        'visible' => 2,
                    ],
                ],
            ],
        ],
    ],
    [
        'title' => '内容管理',
        'icon' => 'layui-icon-read',
        'action' => '',
        'visible' => 1,
        'list' => [
            [
                'title' => '文章管理',
                'icon' => 'layui-icon-form',
                'action' => 'Article/index',
                'visible' => 1,
                'list' => [
                    [
                        'title' => '文章列表',
                        'icon' => 'layui-icon-form',
                        'action' => 'Article/index',
                        'visible' => 1,
                    ],
                    [
                        'title' => '文章栏目',
                        'icon' => 'layui-icon-form',
                        'action' => 'Article/nodes',
                        'visible' => 1,
                    ],
                ],
            ],
            [
                'title' => '投稿管理',
                'icon' => 'layui-icon-form',
                'action' => 'Article/contribute',
                'visible' => sysConfig('users.is_contribute'),
            ],
            [
                'title' => '产品管理',
                'icon' => 'layui-icon-note',
                'action' => 'Goods/index',
                'visible' => 1,
                'list' => [
                    [
                        'title' => '产品列表',
                        'action' => 'Goods/index',
                        'visible' => 1,
                    ],
                    [
                        'title' => '产品分类',
                        'action' => 'Goods/cat',
                        'visible' => 1,
                    ],
                    [
                        'title' => '产品品牌',
                        'action' => 'Goods/brand',
                        'visible' => 2,
                    ],
                ],
            ],
            [
                'title' => '表单管理',
                'icon' => 'layui-icon-form',
                'action' => 'Forms/index',
                'visible' => 1,
                'list' => [
                    [
                        'title' => '表单数据',
                        'action' => 'Forms/index',
                        'visible' => 2,
                    ],
                    [
                        'title' => '表单配置',
                        'action' => 'Forms/config',
                        'visible' => 2,
                    ],
                ],
            ],
        ],
    ],
    [
        'title' => '更多功能',
        'icon' => 'layui-icon-app',
        'action' => 'Index/switch_map',
        'visible' => 1,
        'list' => [],
    ],
    [
        'title' => '高级选项',
        'icon' => 'layui-icon-fonts-code',
        'action' => '',
        'visible' => 0,
        'list' => [
            [
                'title' => '管理员',
                'icon' => 'layui-icon-user',
                'action' => 'System/index',
                'visible' => 1,
            ],
            [
                'title' => '模板管理(可视化)',
                'icon' => 'layui-icon-template-1',
                'action' => 'Template/fileList',
                'visible' => 1,
            ],
            [
                'title' => '备份还原',
                'icon' => 'layui-icon-component',
                'action' => 'System/backup',
                'visible' => 1,
                'list' => [
                    [
                        'title' => '数据备份',
                        'action' => 'System/backup',
                        'visible' => 2,
                    ],
                    [
                        'title' => '数据还原',
                        'action' => 'System/restore',
                        'visible' => 2,
                    ],
                ],
            ],
            [
                'title' => '清除缓存',
                'icon' => 'layui-icon-refresh',
                'action' => 'System/clearCache',
                'visible' => 1,
            ],
        ],
    ],
    [
        'title' => '插件应用',
        'icon' => 'layui-icon-component',
        'action' => 'Plugin/index',
        'visible' => sysConfig('admin.hide_plugin') ? 0 : 1,
        'list' => [
            [
                'title' => '我的插件',
                'action' => 'Plugin/index',
                'visible' => 2,
            ],
            [
                'title' => '插件市场',
                'action' => 'Plugin/store',
                'visible' => 2,
            ],
        ],
    ],
    [
        'title' => '会员中心',
        'icon' => 'layui-icon-username',
        'action' => 'Users/index',
        'visible' => sysConfig('admin.web_user'),
        'list' => [],
    ],
    [
        'title' => '商城中心',
        'icon' => 'layui-icon-cart',
        'action' => '',
        'visible' => 0,
        'list' => [],
    ],
];