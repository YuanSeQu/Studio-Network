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


class Forms extends Base
{
    function getInfo($id = 0) {
        if ($id == 'search') {
            return [
                'action' => getRrzUrl('/search'),
                'type' => $_GET['t'] ?? 'article',
            ];
        }
        if (!$id || !is_numeric($id)) return [];
        $where = ['id' => $id, 'is_del' => 0,];
        $form = M('forms')->where($where)->find();
        if (!$form) return [];
        $form['config'] = unserialize($form['config']) ?: [];
        $form['action'] = getRrzUrl('/formSubmit') . '?id=' . urlencode(base64_encode('formId|' . $id));
        return $form;
    }

    function formSubmit(&$msg = '') {
        $id = I('get.id');
        $id = str_replace('formId|', '', base64_decode($id));
        if (!$id || !is_numeric($id)) {
            $msg = __('表单不存在！');
            return false;
        }
        $where = ['id' => $id, 'is_del' => 0,];
        $form = M('forms')->where($where)->find();
        if (!$form) {
            $msg = __('表单不存在！');
            return false;
        }
        $config = unserialize($form['config']) ?: [];
        $list = [];
        $data = I('post.');
        foreach ($config as $item) {
            if (!isset($data[$item['name']])) continue;

            $value = $data[$item['name']];
            $value = is_array($value) ? implode(',', $value) : trim($value);
            if ($item['required'] && !$value && !is_numeric($value)) {
                $msg = $item['name'] . ' ' . __('不能为空！');
                return false;
            }
            $list[$item['name']] = $value;
        }
        if (!$list) {
            $msg = __('提交的内容无效！');
            return false;
        }
        $msg = __('提交成功！');
        return M('form_data')->insert([
            'form_id' => $id,
            'form_name' => $form['title'],
            'content' => json_encode($list),
            'ip' => getClientIP(),
            'add_time' => time(),
        ]);
    }

    function getFormData($id = 0, $limit = 10) {
        $where = [
            ['form_id', '=', $id]
        ];
        $model = M('form_data')->field('content,add_time')->where($where)->order('id desc');
        $model = $this->setLimit($model, $limit);
        $list = $model->select()->toArray();
        $data = [];
        foreach ($list as $item) {
            $item['content'] = json_decode($item['content'], true) ?: [];
            if (!$item['content']) continue;
            $item['pubtime'] = $item['add_time'];
            $data[] = $item;
        }
        return $data;
    }

    /*
     * HOT热门搜索
     */
    function getHotwords($limit = 5, $maxlength = 10, $days = 30) {
        $where = [
            ['add_time', '>=', strtotime("-{$days} day"),],
            [\think\facade\Db::raw('length(keywords)'), '<=', $maxlength,],
        ];
        $subQuery = M('search_keywords')->where($where)->field('keywords,type')->order('hot desc')->buildSql();
        $list = \think\facade\Db::table($subQuery . ' a')->field('keywords,type')->distinct()->limit($limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['url'] = getRrzUrl('/search') . '?q=' . urlencode($item['keywords']) . '&t=' . $item['type'];
            $item['title'] = $item['keywords'];
        }
        return $list ?: [];
    }

    /*
     * 获取列表筛选条件
     */
    function getFilter($rrz, $page, $field = 'all', $mid = 0, $tid = 0) {
        if (!in_array($page, ['node', 'cat'])) return [];

        $table = $page == 'node' ? 'article_nodes' : 'goods_cat';
        $channelId = $mid ?: (empty($rrz['channel_id']) ? 0 : $rrz['channel_id']);
        if (!$channelId && ($rrz['parent_id'] ?? 0)) {
            $channelId = M($table)->where('id', $rrz['parent_id'])->value('channel_id');
        }
        if (!$channelId && $tid) {
            $channelId = M($table)->where('id', $tid)->value('channel_id');
        }

        $where = ['channel_id' => $channelId, 'is_filter' => 1,];
        if ($field != 'all') {
            $where['name'] = $field;
            unset($where['is_filter']);
        }

        $fields = M('channelfield')->where($where)->field('name,title,dtype,dfvalue')->select()->toArray();

        $filter = $_GET['filter'] ?? [];

        $url = '';
        if ($page && ($tid || ($page == 'cat' && $tid == 0))) {
            $data = ['id' => $tid, 'typeId' => $tid,];
            $url = $this->Route::getRouteUrl($page, $data);
        } elseif (isset($rrz['id']) && is_numeric($rrz['id']) && in_array($this->pageType, ['node', 'cat'])) {
            $data = [
                'id' => $rrz['id'],
                'typeId' => $rrz['id'],
            ];
            $url = $this->Route::getRouteUrl($this->pageType, $data);
        }
        $list = [];
        foreach ($fields as $item) {
            $values = array_values(array_filter(explode('|', $item['dfvalue'])));
            if (!$values) continue;
            $arg = $_GET;
            foreach ($values as &$value) {
                $arg['filter'][$item['name']] = $value;
                $value = [
                    'title' => $value,
                    'class' => (isset($filter[$item['name']]) && $filter[$item['name']] == $value) ? 'on' : '',
                    'url' => $url . ($arg ? '?' . http_build_query($arg) : ''),
                ];
            }
            $arg = $_GET;
            unset($arg['filter'][$item['name']]);
            $arg = array_filter($arg);
            $values = array_merge([
                [
                    'title' => '不限',
                    'class' => isset($filter[$item['name']]) ? '' : 'on',
                    'url' => $url . ($arg ? '?' . http_build_query($arg) : ''),
                ]
            ], $values);
            $list[] = [
                'field' => $item['name'],
                'title' => $item['title'],
                'values' => $values,
            ];
        }
        if ($field != 'all' && $list) return $list[0];
        return $list ?: [];
    }

}