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

class Goods extends Model
{
    /**
     * @param int $id
     * @return array|mixed|\think\facade\Db|Model|null
     * @throws \Exception
     */
    public static function getInfo($id = 0) {
        $info = M('goods')->where('id', $id)->find();

        //标签数据
        $tags = M('tag_rel')->where('rel_id', $id)->where('tag_type', 2)->column('tag_title');
        $info['tags'] = implode(',', $tags);

        return $info;
    }

    /**
     * 批量复制产品
     * @param $ids
     * @param $catId
     * @param int $num
     * @return bool
     * @throws \Exception
     */
    public static function batchCopy($ids, $catId, $num = 1) {
        $data = M('goods')->where('id', 'in', $ids)->select()->toArray();
        foreach ($data as $item) {
            $goodId = $item['id'];
            unset($item['id']);
            $item['cat_id'] = $catId;

            $skus = M('goods_skus')->where('goods_id', $goodId)->select()->toArray();//规格数据
            $attrs = M('goods_attr')->where('goods_id', $goodId)->select()->toArray();//产品参数
            $tags = M('tag_rel')->where('rel_id', $goodId)->where('tag_type', 2)->order('id asc')->select()->toArray();
            if (!$skus && !$attrs && !$tags) {
                $list = [];
                $i = 0;
                while ($i < $num) {
                    $list[] = $item;
                    $i++;
                }
                M('goods')->insertAll($list);
            } else {
                //存在规格或参数 数据
                $i = 0;
                while ($i < $num) {
                    $rId = M('goods')->insert($item, true);
                    if ($rId) {
                        //处理产品规格
                        $list = [];
                        foreach ($skus as $sku) {
                            unset($sku['sku_id']);
                            $sku['goods_id'] = $rId;
                            $list[] = $sku;
                        }
                        $list and M('goods_skus')->insertAll($list);

                        //处理产品参数
                        $list = [];
                        foreach ($attrs as $attr) {
                            unset($attr['id']);
                            $attr['goods_id'] = $rId;
                            $list[] = $attr;
                        }
                        $list and M('goods_attr')->insertAll($list);

                        //处理产品标签
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
     * 保存产品数据后执行操作
     * @param $id
     * @param $data
     * @throws \Exception
     */
    public static function onAfterSave($id, $data) {

        //标签处理
        $tags = I('post.tags');
        $tags = explode(',', $tags);
        $tags = array_map('trim', $tags);
        $tags = array_values(array_unique(array_filter($tags)));
        Tag::saveRelTags($tags, $id, $data['cat_id'], 2);

    }

}