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

namespace app\home\lib;


class Links extends Base
{
    /**
     * 友情链接列表
     * @param int $limit
     * @return mixed
     */
    public function getList($limit = 0) {

        $model = M('site_links')->field('id,title,url,logo,target,email,intro')
            ->where('status', 1)->order('sort asc');

        $model = $this->setLimit($model, $limit);

        $list = $model->select()->toArray();

        foreach ($list as $key => $item) {
            $item['target'] = $item['target'] ? '_blank' : '_self';
            $list[$key] = $item;
        }
        return $list;
    }
}