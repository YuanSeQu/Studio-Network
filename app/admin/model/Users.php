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

namespace app\admin\model;

use think\Model;

class Users extends Model
{

    // 模型初始化
    protected static function init() {
        //TODO:初始化内容
    }

    /**
     * 保存用户属性字段
     * @param int $id
     * @param array $post
     * @param string $msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveAttrField($id = 0, $post = [], &$msg = '') {

        $field = M('users_attribute')->where('id', $id)->find();

        $lib = new \app\admin\lib\Field('users_attr', false);

        $fieldInfo = $lib->saveField($id, $post, $field, $msg);
        if (!$fieldInfo) return false;

        if (is_numeric($id) && $id) {//更新

            $newData = [
                'update_time' => time(),
            ];
            $data = array_merge($post, $newData);
            M('users_attribute')->where('id', $id)->save($data);

            $msg = '操作成功！';
            return true;
        }

        $data = array_merge($post, [
            'sort' => 100,
            'add_time' => time(),
            'update_time' => time(),
        ]);
        $rs = M('users_attribute')->insert($data);
        if (!$rs) {
            $msg = '操作失败！';
            return false;
        }

        $msg = '操作成功！';
        return true;
    }


    /**
     * 删除用户属性字段信息
     * @param $id
     * @param string $msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delAttrField($id, &$msg = '') {
        if (!is_numeric($id)) {
            $msg = '参数有误！';
            return false;
        }
        $row = M('users_attribute')->where('id', $id)->find();
        if (!$row) {
            $msg = '属性不存在！';
            return false;
        }
        if ($row['is_system']) {
            $msg = '该属性是系统属性，不能删除！';
            return false;
        }
        $lib = new \app\admin\lib\Field('users_attr', false);

        $rs = $lib->delField($row['name'], $msg);
        if (!$rs) return false;

        $rs = M('users_attribute')->where('id', $id)->delete();
        if (!$rs) {
            $msg = '删除失败！';
            return false;
        }

        $msg = '删除成功！';
        return true;
    }

    /*
     * 预处理字段值
     */
    public function checkAttrFieldValue($data = []) {
        //取出自定义字段
        $fields = M('users_attribute')->order('sort asc,id asc')->select()->toArray();

        $lib = new \app\admin\lib\Field('users_attr', false);

        return $lib->checkFieldValue($fields, $data);
    }


}