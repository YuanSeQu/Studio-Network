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


class base
{
    /**
     * 数据
     * @var Collection
     */
    protected $data;

    /**
     * 表名
     * @var string
     */
    protected $table = '';

    /**
     * 缓存键值
     * @var string
     */
    private $cacheKey = 'Dm_';

    /**
     * 条件
     * @var array
     */
    protected $where = [];

    /**
     * 初始化数据
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct() {
        if (!$this->table) {
            $this->data = new Collection();
            return;
        }
        $this->cacheKey = 'Dm_' . $this->table;
        $data = F($this->cacheKey);
        if ($data) {
            $this->data = new Collection($data);
        } else {
            $this->updateCache();
        }
    }

    /**
     * 更新数据缓存
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateCache() {
        if (!$this->table) return false;
        $model = M($this->table);
        if ($this->where) {
            $model = $model->where($this->where);
        }
        $data = $model->select()->toArray();
        if ($data) {
            F($this->cacheKey, $data);
            $this->data = new Collection($data);
        } else {
            $this->data = new Collection([]);
        }
        return true;
    }

    /**
     * 获取数据，拷贝数据保持原始数据不变
     * @param bool $cache 是否获取缓存
     * @return Collection
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getData($cache = true): Collection {
        if (!$cache) {
            $this->updateCache();
        }
        return clone $this->data;
    }

    /**
     * 其他方法 直接对接 Collection
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments) {
        if (!method_exists($this->data, $name)) {
            return false;
        }
        return call_user_func_array([clone $this->data, $name], $arguments);
    }
}