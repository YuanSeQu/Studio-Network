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

class Channel extends Model
{
    protected $name = 'channeltype';

    // 模型初始化
    protected static function init() {
        //TODO:初始化内容
    }

    /**
     * 保存模型字段
     * @param int $id
     * @param array $post
     * @param string $msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveField($id = 0, $post = [], &$msg = '') {

        $channel_id = $post['channel_id'];
        if (!is_numeric($channel_id) || !$channel_id) {
            $msg = '参数有误！';
            return false;
        }

        $channel = M('channeltype')->where('id', $channel_id)->find();
        if (!$channel) {
            $msg = '参数错误，模型不存在！';
            return false;
        }
        $field = M('channelfield')->where('id', $id)->find();
        $tableName = $channel['type'];

        $lib = new \app\admin\lib\Field($tableName, true);

        $fieldInfo = $lib->saveField($id, $post, $field, $msg);
        if (!$fieldInfo) return false;

        if (is_numeric($id) && $id) {//更新

            $newData = [
                'maxlength' => $fieldInfo['maxlength'],
                'define' => $fieldInfo['buideType'],
                'update_time' => time(),
            ];
            $data = array_merge($post, $newData);
            M('channelfield')->where('id', $id)->save($data);

            $msg = '操作成功！';
            return true;
        }

        $data = array_merge($post, [
            'maxlength' => $fieldInfo['maxlength'],
            'define' => $fieldInfo['buideType'],
            'sort' => 100,
            'add_time' => time(),
            'update_time' => time(),
            'channel_type' => $channel['type'],
            'channel_id' => $channel_id,
        ]);
        $rs = M('channelfield')->insert($data);
        if (!$rs) {
            $msg = '操作失败！';
            return false;
        }
        $msg = '操作成功！';
        return true;
    }

    /**
     * 删除模型字段
     * @param $id
     * @param string $msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delField($id, &$msg = '') {

        if (!is_numeric($id)) {
            $msg = '参数有误！';
            return false;
        }
        $row = M('channelfield')->where('id', $id)->find();
        if (!$row) {
            $msg = '字段数据不存在！';
            return false;
        }

        $lib = new \app\admin\lib\Field($row['channel_type'], true);

        $rs = $lib->delField($row['name'], $msg);
        if (!$rs) return false;

        $rs = M('channelfield')->where('id', $id)->delete();
        if (!$rs) {
            $msg = '删除失败！';
            return false;
        }

        $msg = '删除成功！';
        return true;
    }

    /**
     * 预处理字段值
     * @param int $typeId 分类或栏目ID
     * @param string $type 模型的类别：articles=文章模型，goods=产品模型
     * @param array $data 需要处理的数据集
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkFieldValue($typeId = 0, $type = '', $data = []) {

        if (!is_numeric($typeId) || !$typeId || !in_array($type, ['articles', 'goods'])) {
            return $data;
        }
        $table = $type == 'articles' ? 'article_nodes' : 'goods_cat';
        $info = M($table)->where('id', $typeId)->field('channel_id,parent_id')->find();
        $channelId = $info ? $info['channel_id'] : 0;
        //查找模型，向上查找一级
        if (!$channelId && $info['parent_id']) {
            $channelId = M($table)->where('id', $info['parent_id'])->value('channel_id');
        }
        //没有模型
        if (!$channelId) return $data;
        //取出自定义字段
        $fields = M('channelfield')->where('channel_id', $channelId)->order('sort asc,id asc')->select()->toArray();

        $lib = new \app\admin\lib\Field($table, true);

        return $lib->checkFieldValue($fields, $data);
    }


}