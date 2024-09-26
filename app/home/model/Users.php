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

namespace app\home\model;

use think\Model;

class Users extends Model
{

    // 模型初始化
    protected static function init() {
        //TODO:初始化内容
    }


    /**
     * 验证会员属性必填项
     * @param array $attr
     * @param string $type
     * @param string $msg
     * @return bool
     * @throws \Exception
     */
    public function isAttrRequired(&$attr = [], $type = '', &$msg = '') {

        if (!$attr) return true;

        $where = [
            ['is_hidden', '=', 0],
            ['is_required', '=', 1,],
        ];
        if ($type == 'reg') {
            $where[] = ['is_reg', '=', 1];
        }

        $fields = M('users_attribute')->field('title,name,dtype,is_required')
            ->where($where)->order('sort asc,id asc')->select()->toArray();

        foreach ($fields as $item) {
            if (isset($attr[$item['name']])) {
                if (is_array($attr[$item['name']])) {
                    $attr[$item['name']] = implode(',', $attr[$item['name']]);
                }
                $value = trim($attr[$item['name']]);
                if (empty($value)) {
                    $msg = $item['title'] . '不能为空！';
                    return false;
                }
                $attr[$item['name']] = $value;
            }
        }

        return true;
    }

}