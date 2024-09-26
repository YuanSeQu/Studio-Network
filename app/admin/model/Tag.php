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

class Tag extends Model
{

    /**
     * @param array $tags
     * @param int $relId
     * @param int $typeId
     * @param string $type
     * @throws \think\db\exception\DbException
     */
    public static function saveRelTags($tags = [], $relId = 0, $typeId = 0, $type = '1') {

        if ($tags) {
            //先进行新增判断
            self::addAll($tags, $type);
        }

        //先请空
        $where = [
            ['rel_id', '=', $relId],
            ['tag_type', '=', $type],
        ];
        $oldIds = M('tag_rel')->where($where)->column('tag_id');//取出原始关联id

        M('tag_rel')->where($where)->save(['type_id' => $typeId,]);

        $tags = M('tag')->where('type', $type)->whereIn('title', $tags)->field('id,title')->select()->toArray();
        $list = [];
        foreach ($tags as $item) {
            //新增的
            if (!in_array($item['id'], $oldIds)) {
                $list[] = [
                    'tag_id' => $item['id'],
                    'rel_id' => $relId,
                    'type_id' => $typeId,
                    'tag_type' => $type,
                    'tag_title' => $item['title'],
                    'last_modify' => time(),
                ];
            }
        }
        if ($list) {
            M('tag_rel')->insertAll($list);
            $newIds = array_column($list, 'tag_id');
            M('tag')->whereIn('id', $newIds)->inc('total', 1)->update();//增加文章数
        }

        //修改后的id
        $ids = array_column($tags, 'id');
        //删除的
        $delIds = array_diff($oldIds, $ids);
        if ($delIds) {
            $where[] = ['tag_id', 'in', $delIds];
            if (M('tag_rel')->where($where)->delete()) {
                M('tag')->whereIn('id', $delIds)->dec('total', 1)->update();//减少文章数
            }
        }
    }

    /**
     * 批量插入
     * @param array $tags
     * @param string $type
     * @return bool|int
     */
    public static function addAll($tags = [], $type = '1') {
        $tags = array_values(array_unique($tags));
        $list = [];
        foreach ($tags as $item) {
            $item = trim($item);
            if ($item && strlen($item) < 30) {
                $count = M('tag')->where('title', $item)->where('type', $type)->count();
                if (!$count) {
                    $list[] = ['title' => $item, 'type' => $type, 'add_time' => time(),];
                }
            }
        }
        if ($list) {
            return M('tag')->insertAll($list);
        }
        return true;
    }


}