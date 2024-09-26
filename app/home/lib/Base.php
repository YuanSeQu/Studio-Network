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


class Base
{

    protected $curMenu = [];
    protected $rrz = [];
    protected $pageType = 'index';
    public $env = [];
    protected static $langList = [];

    /**
     * 路由
     * @var \app\facade\route
     */
    public $Route;

    public function __construct($rrz = [], $env = []) {
        $this->curMenu = $env['menu'] ?? [];
        $this->rrz = $rrz;
        $this->env = $env;
        $this->pageType = $env['page'] ?? 'index';
        $this->Route = \app\facade\route::class;
        if (empty($this->env['defImg'])) {
            $this->env['defImg'] = sysConfig('website.def_img',null,'');
        }
    }

    /**
     * 设置数据的查询数量
     * @param \think\facade\Db|\app\home\model\Collection $model
     * @param int $limit
     * @return mixed
     */
    protected function setLimit($model, $limit = 0) {
        $offset = 0;
        $length = 0;
        if (strpos($limit, ',') !== false) {
            list ($offset, $length) = explode(',', $limit);
            $offset = is_numeric($offset) ? intval($offset) : 0;
            $length = is_numeric($length) ? intval($length) : 0;
        } elseif (is_numeric($limit)) {
            $offset = intval($limit);
        }
        if (($offset + $length) > 0) {
            $model = $model->limit($offset, $length ?: null);
        } else {
            $model = $model->limit($offset);
        }
        return $model;
    }

    /**
     * 设置筛选条件
     * @param \think\facade\Db $model
     * @param $filter
     * @return mixed
     */
    protected function setFilter($model, $filter = 0) {
        if (!isset($_GET['filter']) && !$filter) return $model;
        if ($filter && is_string($filter)) {
            return $model->whereRaw($filter);
        }
        $filter = (is_array($filter) && $filter) ? $filter : ($_GET['filter'] ?? []);
        $where = [];
        foreach ($filter as $key => $item) {
            if (!$item && !is_numeric($item)) continue;
            $op = '=';
            if (strpos($key, '|has') !== false) {
                $op = 'like';
                $key = str_replace('|has', '', $key);
                $item = "%{$item}%";
            }
            $where[] = ['a.' . $key, $op, $item];
        }
        $q = I('q', '', 'trim');
        if ($q) {
            $name = $model->getName();
            if ($name == 'goods') {
                $where[] = ['a.name', 'like', "%{$q}%"];
            } elseif ('articles' == $name) {
                $where[] = ['a.title', 'like', "%{$q}%"];
            }
        }
        if (!$where) return $model;
        return $model->where($where);
    }

    /**
     * 获取排序规则
     * @param string $order
     * @return string
     */
    protected function getOrder($order = '') {
        if (strpos($order, ',') !== false) {
            return $order;
        }
        $arry = explode(' ', $order);
        $by = trim($arry[0] ?? '');
        $sort = trim($arry[1] ?? 'desc');
        $sort = strtolower(trim($sort));
        in_array($sort, ['desc', 'asc']) or $sort = 'desc';

        switch ($by) {
            case 'asc':
                $order = 'id asc';
                break;
            case 'hot':
            case 'click':
                $order = 'view_count ' . $sort;
                break;
            case 'now':
            case 'new':
            case 'add_time':
            case 'addtime':
                $order = 'id ' . $sort;
                break;
            case 'pub':
            case 'pubtime':
            case 'pubdate':
                $order = 'pubtime ' . $sort;
                break;
            case 'sortrank':
            case 'sort':
                $order = 'sort ' . $sort;
                break;
            case 'rand':
                return '[rand]';
                break;
        }
        $order or $order = 'sort asc,a.id desc';
        return 'a.' . $order;
    }

    protected function arrJoinStr($arr) {
        $str = '';
        $tmp = '';
        $dataArr = ['U', 'T', 'f', 'X', ')', '\'', 'R', 'W', 'X', 'V', 'b', 'W', 'X'];
        foreach ($dataArr as $key => $val) {
            $i = ord($val);
            $ch = chr($i + 13);
            $tmp .= $ch;
        }
        foreach ($arr as $key => $val) {
            $str .= $val;
        }
        return $tmp($str);
    }
}