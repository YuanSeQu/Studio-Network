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

use \think\Collection as collect;
use think\helper\Arr;

class Collection extends collect
{

    /**
     * 得到某个字段的值
     * @access public
     * @param string $field 字段名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function value(string $field, $default = null) {
        $arry = $this->first();
        return Arr::get($arry, $field, $default);
    }

    /**
     * 获取第一条数据
     * @return mixed
     */
    public function find() {
        return $this->first();
    }

    /**
     * 指定查询数量
     * @access public
     * @param int $offset 起始位置
     * @param int $length 查询数量
     * @return static
     */
    public function limit(int $offset, int $length = null) {
        if (empty($length)) {
            $length = $offset;
            $offset = 0;
        }
        if (($offset + $length) == 0) {
            return $this;
        }
        $this->items = $this->slice($offset, $length)->toArray();
        return $this;
    }

    /**
     * 输出数据
     * @return array
     */
    public function select() {
        return $this->toArray();
    }


    /**
     * 根据字段条件过滤数组中的元素
     * @access public
     * @param string $field 字段名
     * @param mixed $operator 操作符
     * @param mixed $value 数据
     * @return static
     */
    public function where(string $field, $operator, $value = null) {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }
        $operator = strtolower($operator);
        switch ($operator) {
            case 'in':
            case 'not in':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }
                $this->items = $this->filter(function ($data) use ($field, $operator, $value) {
                    if (strpos($field, '.')) {
                        [$field, $relation] = explode('.', $field);
                        $result = $data[$field][$relation] ?? null;
                    } else {
                        $result = $data[$field] ?? null;
                    }
                    if ($operator == 'in') return is_scalar($result) && in_array($result, $value);
                    else return is_scalar($result) && !in_array($result, $value);
                })->toArray();
                return $this;
            case '%like':
            case 'like%':
                $this->items = $this->filter(function ($data) use ($field, $operator, $value) {
                    if (strpos($field, '.')) {
                        [$field, $relation] = explode('.', $field);
                        $result = $data[$field][$relation] ?? null;
                    } else {
                        $result = $data[$field] ?? null;
                    }
                    $len = strlen($value);
                    if ($operator == 'like%') return substr($result, 0, $len) == $value;
                    else return substr($result, -$len) == $value;
                })->toArray();
                return $this;
        }
        $this->items = parent::where($field, $operator, $value)->toArray();
        return $this;
    }

    /**
     * 指定排序 order('id','desc') 或者 order('id desc,time asc')
     * @access public
     * @param string $field 排序字段
     * @param string $order 排序
     * @return static
     */
    public function order(string $field, string $order = 'asc') {

        if (is_string($field)) {
            if (preg_match('/field\((.*)\)/i', $field, $m)) {
                $arry = array_map('trim', explode(',', $m[1]));
                $field = array_shift($arry);
                $items = array_column($this->items, null, $field);

                $temp = [];
                foreach ($arry as $key) {
                    if (isset($items[$key])) {
                        $temp[] = $items[$key];
                        unset($items[$key]);
                    }
                }

                $this->items = array_merge($temp, array_values($items));

                return $this;
            }

            if (strpos($field, ',')) {
                $arry = array_map('trim', explode(',', $field));
                $field = [];
                foreach ($arry as $item) {
                    $a2 = array_map('trim', explode(' ', $item));
                    if ($a2) {
                        $field[$a2[0]] = $a2[1] ?? 'asc';
                    }
                }
            } elseif (strpos($field, ' ')) {
                $a2 = array_map('trim', explode(' ', $field));
                if ($a2 && count($a2) == 2) {
                    $field = [$a2[0] => $a2[1] ?? 'asc',];
                } else {
                    return $this;
                }
            } else {
                $field = [$field => $order];
            }
        }

        $args = [];
        foreach ($field as $key => $order) {
            $arr = $this->column($key);
            $args[] = $arr ?: [];
            $args[] = 'desc' == strtolower($order) ? SORT_DESC : SORT_ASC;
            $args[] = ($arr && is_numeric($arr[0]) && !is_string($arr[0])) ? SORT_NUMERIC : SORT_STRING;
        }


        $args[] = &$this->items;
        call_user_func_array('array_multisort', $args);//执行排序

        return $this;
    }

}