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

namespace app\admin\controller;

use app\BaseController;


class Base extends BaseController
{
    protected $account;

    var $pagedata = [
        'columns' => [],//展示列
        'tabs' => [],//页签
        'search' => [],//搜索项
        'toolbar_html' => '',//自定义
        'actions' => [],//按钮列表
        'model' => null,//数据模型
        'fields' => '',//查询字段
        'where' => null,//查询条件
        'order' => null,//排序规则
        'group' => null,//分组查询
        'ajax_url' => '',//分页查询等ajax数据请求地址
        'limit' => 10,//分页每页数据
        'pk_field' => null,//主键，如果不赋值则自动获取
        'checkType' => false,//选择按钮类型，不显示则为false，默认：false，亦可设置：checkbox，radio
        'fixedColumn' => false,//固定列宽:是否
        'colsNo' => false,//列序号,是否显示序号
        'grid_class' => '',//样式名称
        'grid_desc' => '',//其他内容
        'isPage' => true,//是否分页
        'data' => [],//表格数据
        'trAttr' => [],//表格行属性 ['attrName'=>'fieldName']
    ];

    protected function initialize() {
        parent::initialize();
        $this->account = $this->getAccount();
    }

    /**
     * 检测id是否合法参数
     * @param string $id
     * @return array|string
     * @throws \Exception
     */
    protected function checkIds($id = '') {
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $id = array_values(array_filter($id, 'is_numeric'));
            $id or $this->error('参数不合法！');
        } else {
            is_numeric($id) or $this->error('参数不合法！');
        }
        return $id;
    }

    /**
     * 获取当前登陆用户信息
     * @param bool $isLogin 判断登陆情况
     * @return mixed|void
     */
    protected function getAccount($isLogin = true) {
        $account = parent::getAccount();
        if ($isLogin && !$account) {
            if ($this->request->isAjax()) {
                $this->request->isJson() or exit('no_login');
                exit(json_encode(['code' => 'no_login',]));
            } else {
                $this->redirect(U('Login/index'));
            }
            return;
        }
        return $account;
    }

    /**
     * 输出表格
     * @param string $template
     * @return false|string
     * @throws \Exception
     */
    protected function grid_fetch($template = '') {
        $this->pagedata['columns'] = array_filter((array)$this->pagedata['columns']);
        $model = $this->setModel();//设置model
        $gridConf = $this->getGridData($model);//获取grid数据

        $tabs = I('istabs', true);//是否显示tabs
        $this->pagedata['tabs'] = (!$tabs || $tabs === 'false' || $tabs === 0) ? false : $this->pagedata['tabs'];//头部tabs

        $this->pagedata['toolbar_html'] = $this->pagedata['toolbar_html'] ? $this->fetch($this->pagedata['toolbar_html']) : '';//查询部件的自定义html
        $this->pagedata['ajax_url'] or $this->pagedata['ajax_url'] = $this->app->request->url();//没有设置时，设置__SELF__路径为默认值

        if ($this->pagedata['checkType'] === true) {
            $this->pagedata['checkType'] = 'checkbox';//grid展示多选框还是单选框
        } elseif (($checkType = I('checkType')) && in_array($checkType, ['checkbox', 'radio'])) {
            $this->pagedata['checkType'] = $checkType;
        }

        $this->pagedata['tabs'] = array_values(array_filter((array)$this->pagedata['tabs']));
        $this->pagedata['search'] = array_values(array_filter((array)$this->pagedata['search']));
        $this->pagedata['actions'] = array_values(array_filter((array)$this->pagedata['actions']));

        $this->assign(array_merge($this->pagedata, $gridConf, [
            'columns' => $this->pagedata['columns'],//grid展示列
            'cols_nums' => count($this->pagedata['columns']),//列数量
        ]));

        if ($this->request->isJson()) {
            return json([
                'data' => $this->fetch('admin@base/grid/tbody'),
                'count' => $gridConf['count'],
                'curr' => $gridConf['curr'] ?: 1,
                'page_count' => $gridConf['page_count'],
            ]);
        }
        $this->checkTemplateFile($template) and $this->assign('grid_desc', $this->fetch($template));

        return $this->fetch('admin@base/grid');
    }

    private function checkTemplateFile(string $template) {
        $driver = call_user_func([$this->view, 'engine']);
        $path = $driver->getConfig('view_path');
        if (empty($path)) {
            $view = $driver->getConfig('view_dir_name');
            if (is_dir($this->app->getAppPath() . $view)) {
                $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $appName = $this->app->http->getName();
                $path = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }
            $driver->config(['view_path' => $path]);
        }
        return $driver->exists($template);
    }

    private function setModel($model = null) {
        $model = $model ? $model : (is_string($this->pagedata['model']) ? M($this->pagedata['model']) : $this->pagedata['model']);//获取model
        //设置主键
        if ($this->pagedata['pk_field']) {
            $pk_field = $this->pagedata['pk_field'];
        } elseif ($model) {
            $pk_field = $model->getPk();
            is_array($pk_field) and $pk_field = $pk_field[0];
        }
        $model and $this->pagedata['pk_field'] = $pk_field;

        //获取查询字段
        if (!$this->pagedata['fields'] && $model) {
            $field = $model->getOptions('field');
            if (!isset($field) || !$field) {
                $m_fields = $model->getTableFields();
                $fields = array_column($this->pagedata['columns'], 'field');
                $fields = implode(',', $fields);
                $fields = explode(',', $fields);
                $fields = array_intersect($fields, $m_fields);
                $this->pagedata['fields'] = array_unique(array_merge($fields, [$pk_field]));
            }
        }
        if ($model) {
            //设置model的查询字段
            if ($this->pagedata['fields']) {
                $model = $model->field($this->pagedata['fields']);
            }
            //设置model的查询条件
            $search = $_POST['search'] ?? [] and $search = $this->getFilter($search);
            $where = $this->pagedata['where'];
            if (is_string($where)) {
                $model->whereRaw($where);
                $where = [];
            }
            $search = array_merge($where ?: [], $search ?: []);
            $search and $model = $model->where($search);
        }
        return $model;
    }

    /**
     * 处理查询条件，
     * @param mixed $search 查询数据
     * @return mixed 返回处理后的查询条件
     */
    protected function getFilter($search) {
        $where = [];
        foreach ($search as $key => $item) {
            if (!$item && !is_numeric($item)) continue;

            if (strpos($key, '|') !== false) {
                $keys = explode('|', $key);
                if (isset($keys[2]) && function_exists($keys[2])) {
                    if (is_array($item)) {
                        $item = array_map($keys[2], $item);
                    } else {
                        $item = $keys[2]($item);
                    }
                }
                if ($_item = $this->getFilterType($keys[0], $keys[1], $item)) {
                    $where[] = $_item;
                }
            } elseif ($_item = $this->getFilterType($key, 'eq', $item)) {
                $where[] = $_item;
            }
        }
        return $where;
    }

    /**
     * 组织查询条件
     * @param mixed $field 字段名称
     * @param mixed $type 类型
     * @param mixed $var 值
     * @return mixed 返回条件
     */
    protected function getFilterType($field, $type, $var) {
        $filterArray = [
            'eq' => ['=', $var],//等于（=）
            'neq' => ['<>', $var],//不等于（<>）
            'gt' => ['>', $var],//大于（>）
            'egt' => ['>=', $var],//大于等于（>=）
            'lt' => ['<', $var],//小于（<）
            'elt' => ['<=', $var],//小于等于（<=）
            'like' => ['like', $type == 'like' ? '%' . $var . '%' : ''],//全模糊查询
            '%like' => ['like', $type == '%like' ? '%' . $var : ''],//前模糊查询
            'like%' => ['like', $type == 'like%' ? $var . '%' : ''],//后模糊查询
            '%like%' => ['like', $type == '%like%' ? '%' . $var . '%' : ''],//全模糊查询
            'has' => ['like', $type == 'has' ? '%' . $var . '%' : ''],//全模糊查询
            'head' => ['like', $type == 'head' ? '%' . $var : ''],//前模糊查询
            'foot' => ['like', $type == 'foot' ? $var . '%' : ''],//后模糊查询
            'nohas' => ['not like', $type == 'nohas' ? '%' . $var . '%' : ''],//模糊查询反向
            'notlike' => ['not like', $type == 'notlike' ? '%' . $var . '%' : ''],//模糊查询反向
            'between' => ['between', $var],//区间查询
            'notbetween' => ['not between', $var],//（不在）区间查询
            'in' => ['in', $var],//IN 查询
            'notin' => ['not in', $var],//（不在）IN 查询
        ];
        $filter = $filterArray[$type] ?? false;
        $filter and array_unshift($filter, $field);
        return $filter;
    }

    /**
     * 获取grid分页相关数据并引用返回数据和数据总数量。
     * @param mixed $model model
     * @return mixed 返回分页相关数据
     */
    private function getGridData($model) {
        $limit = cookie('PAGE_SIZE');//获取cookie中记录的页数。
        $limit = $limit ? $limit : $this->pagedata['limit'];//每页数据量，如果用户没有设置，则取默认值。

        $curr = I('post.page', 1);//当前页
        $curr = $curr ? $curr : 1;//当前页，默认1
        $count = 0;
        $data = [];
        if (isset($this->pagedata['dataCallBack']) && $this->pagedata['dataCallBack']) {
            $offset = $limit * ($curr - 1);
            $data = $this->pagedata['dataCallBack']($offset, $limit, $count);
        } elseif ($model) {
            $count_model = clone $model;
            $count = $count_model->count();
            if ($this->pagedata['order']) {
                $model = $model->order($this->pagedata['order']);
            }
            $data = $model->page($curr, $limit)->select()->toArray();
        }
        $this->pagedata['data'] = $data ?: $this->pagedata['data'] ?: [];
        $count = $count ?: count($this->pagedata['data']);
        return [
            'count' => $count,//总数量
            'page_count' => ceil($count / $limit),//总页数
            'limit' => $limit,//每页数据量
            'curr' => $curr,//当前页数
        ];
    }
}