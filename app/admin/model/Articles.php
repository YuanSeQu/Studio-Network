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

class Articles extends Model
{

    /**
     * @param int $id
     * @return array|mixed|\think\facade\Db|Model|null
     * @throws \Exception
     */
    public static function getInfo($id = 0) {
        $info = M('articles')->where('id', $id)->find();

        //标签数据
        $tags = M('tag_rel')->where('rel_id', $id)->where('tag_type', 1)->order('id asc')->column('tag_title');
        $info['tags'] = implode(',', $tags);

        return $info;
    }

    /**
     * 批量复制文章
     * @param $ids string/array 复制文章的id集合
     * @param $nodeId int 复制到栏目
     * @param int $num int 复制几遍
     * @return bool
     * @throws \Exception
     */
    public static function batchCopy($ids, $nodeId, $num = 1) {
        $data = M('articles')->where('id', 'in', $ids)->select()->toArray();
        foreach ($data as $item) {
            $id = $item['id'];
            unset($item['id']);
            $item['node_id'] = $nodeId;

            $tags = M('tag_rel')->where('rel_id', $id)->where('tag_type', 1)->order('id asc')->select()->toArray();
            if (!$tags) { //不存在标签直接批量插入
                $list = [];
                $i = 0;
                while ($i < $num) {
                    $list[] = $item;
                    $i++;
                }
                M('articles')->insertAll($list);
            } else {
                //存在标签
                $i = 0;
                while ($i < $num) {
                    $rId = M('articles')->insert($item, true);
                    if ($rId){
                        //标签数据
                        $list = [];
                        foreach ($tags as $val) {
                            unset($val['id']);
                            $val['rel_id'] = $rId;
                            $list[] = $val;
                        }
                        $list and M('tag_rel')->insertAll($list);
                    }
                    $i++;
                }
            }
        }
        return true;
    }

    /**
     * 保存文章数据后执行操作
     * @param $id
     * @param $data
     * @throws \think\db\exception\DbException
     */
    public static function onAfterSave($id, $data) {

        //标签处理
        $tags = I('post.tags');
        $tags = explode(',', $tags);
        $tags = array_map('trim', $tags);
        $tags = array_values(array_unique(array_filter($tags)));
        Tag::saveRelTags($tags, $id, $data['node_id'], 1);

    }
}