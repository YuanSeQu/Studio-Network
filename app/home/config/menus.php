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
 * 会员中心菜单配置
 */

return [
    [
        'title' => '会员中心',
        'visible' => 1,
        'list' => [
            [
                'title' => '个人中心',
                'action' => 'user/index',
                'visible' => 1,
            ],
            [
                'title' => '我的信息',
                'action' => 'user/info',
                'visible' => 1,
            ],
        ],
    ],
    [
        'title' => '商城中心',
        'visible' => 0,
        'list' => [
            [
                'title' => '购物车',
                'action' => '',
                'visible' => 1,
            ],
            [
                'title' => '收货地址',
                'action' => '',
                'visible' => 1,
            ],
            [
                'title' => '我的订单',
                'action' => '',
                'visible' => 1,
            ],
            [
                'title' => '我的评价',
                'action' => '',
                'visible' => 1,
            ],
        ],
    ],
    [
        'title' => '投稿中心',
        'visible' => sysConfig('users.is_contribute'),
        'list' => [
            [
                'title' => '投稿列表',
                'action' => 'user/articles',
                'visible' => 1,
            ],
            [
                'title' => '我要投稿',
                'action' => 'user/addArticle',
                'visible' => 1,
            ],
        ],
    ],
];