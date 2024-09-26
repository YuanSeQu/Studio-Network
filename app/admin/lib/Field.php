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

namespace app\admin\lib;

use think\facade\Db;

class Field
{

    /**
     * 操作的表名称
     * @var string
     */
    protected $tableName = '';

    /**
     * 表前缀
     * @var string
     */
    protected $prefix = 'rrz_';

    /**
     * 自动是否判断筛选功能
     * @var bool
     */
    protected $isFilter = false;

    /**
     * 字段类型
     * @var array
     */
    protected $fieldType = [];

    public function __construct($tableName = '', $is_filter = false) {
        $this->tableName = $tableName;
        $this->isFilter = $is_filter;
        $this->prefix = Db::getConnection()->getConfig('prefix');
        $this->fieldType = C('field.type');
    }


    /**
     * 获得字段创建信息
     * @param string $dtype 字段类型
     * @param string $name 字段名称
     * @param string $dfvalue 默认值
     * @param string $title 字段标题
     * @return array
     */
    function GetFieldMake($dtype, $name, &$dfvalue, $title) {
        $fields = [];

        //如果是数字型，则判断默认值类型是否符合，不符合则设置为0
        if (in_array($dtype, ['int', 'float', 'decimal'])) {
            if (!is_numeric($dfvalue) || !$dfvalue) {
                $dfvalue = 0;
            }
            $dfvalue = $dtype == 'int' ? intval($dfvalue) : round($dfvalue, 2);
        }

        if ("int" == $dtype) {
            $maxlen = 10;
            $fields['sql'] = " `{$name}` int({$maxlen}) NOT NULL DEFAULT {$dfvalue} COMMENT '{$title}';";
            $fields['buideType'] = "int({$maxlen})";
        } else if ("datetime" == $dtype) {
            if (preg_match("#[0-9\-]#", $dfvalue)) {
                $dfvalue = strtotime($dfvalue);
                empty($dfvalue) and $dfvalue = 0;
            } else {
                $dfvalue = 0;
            }
            $maxlen = 11;
            $fields['sql'] = " `{$name}` int({$maxlen}) unsigned NOT NULL DEFAULT {$dfvalue} COMMENT '{$title}';";
            $fields['buideType'] = "int({$maxlen})";
        } else if ("switch" == $dtype) {
            if (empty($dfvalue) || preg_match("#[^0-9]#", $dfvalue)) {
                $dfvalue = 1;
            }
            $maxlen = 1;
            $fields['sql'] = " `{$name}` tinyint({$maxlen}) unsigned NOT NULL DEFAULT {$dfvalue} COMMENT '{$title}';";
            $fields['buideType'] = "tinyint({$maxlen})";
        } else if ("float" == $dtype) {
            $maxlen = 9;
            $fields['sql'] = " `{$name}` float({$maxlen},2) NOT NULL DEFAULT {$dfvalue} COMMENT '{$title}';";
            $fields['buideType'] = "float({$maxlen},2)";
        } else if ("decimal" == $dtype) {
            $maxlen = 10;
            $fields['sql'] = " `{$name}` decimal({$maxlen},2) NOT NULL DEFAULT {$dfvalue} COMMENT '{$title}';";
            $fields['buideType'] = "decimal({$maxlen},2)";
        } else if ("img" == $dtype) {
            $maxlen = 255;
            $fields['sql'] = " `{$name}` varchar({$maxlen}) NOT NULL DEFAULT '' COMMENT '{$title}';";
            $fields['buideType'] = "varchar({$maxlen})";
        } else if ("imgs" == $dtype) {
            $maxlen = 0;
            $fields['sql'] = " `{$name}` text COMMENT '{$title}';";
            $fields['buideType'] = "text";
        } else if ("media" == $dtype) {
            $maxlen = 255;
            $fields['sql'] = " `{$name}` varchar({$maxlen}) NOT NULL DEFAULT '' COMMENT '{$title}';";
            $fields['buideType'] = "varchar({$maxlen})";
        } else if ("files" == $dtype) {
            $maxlen = 0;
            $fields['sql'] = " `{$name}` text COMMENT '{$title}';";
            $fields['buideType'] = "text";
        } else if ("multitext" == $dtype) {
            $maxlen = 0;
            $fields['sql'] = " `{$name}` text COMMENT '{$title}';";
            $fields['buideType'] = "text";
        } else if ("html" == $dtype) {
            $maxlen = 0;
            $fields['sql'] = " `{$name}` longtext COMMENT '{$title}';";
            $fields['buideType'] = "longtext";
        } else if ("checkbox" == $dtype) {
            $maxlen = 0;
            $fields['sql'] = " `{$name}` text COMMENT '{$title}';";
            $fields['buideType'] = "text";
        } else if ("select" == $dtype || "radio" == $dtype) {
            $maxlen = 255;
            $fields['sql'] = " `{$name}` varchar({$maxlen}) NOT NULL DEFAULT '' COMMENT '{$title}';";
            $fields['buideType'] = "varchar({$maxlen})";
        } else {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields['sql'] = " `{$name}` varchar({$maxlen}) NOT NULL DEFAULT '{$dfvalue}' COMMENT '{$title}';";
            $fields['buideType'] = "varchar({$maxlen})";
        }
        $fields['maxlength'] = $maxlen;
        return $fields;
    }

    /**
     * 检测频道模型相关的表字段是否已存在
     * @param string $table 表
     * @param string $name 字段名
     * @param array $whitelist 白名单
     * @return bool
     */
    public function checkChannelFieldList($table, $name, $whitelist = []) {
        if (!$name) return true;
        if (is_array($whitelist) && $whitelist && in_array($name, $whitelist)) {
            return false;
        }
        $fields = M($table)->getTableFields();
        $def = C('field.disable');
        $fields = array_merge($fields, $def);
        return in_array($name, $fields);
    }

    /**
     * 检测表中是否存在某个字段
     * @param string $table
     * @param string $name
     * @return bool
     */
    public function checkTableFieldList($table, $name) {
        if (!$name || !$table) return false;
        $fields = M($table)->getTableFields();
        return in_array($name, $fields);
    }

    /**
     * 检测保存的数据
     * @param array $post
     * @param string $msg
     * @return array|bool
     */
    public function checkSaveData($post = [], &$msg = '') {
        if (empty($post['dtype']) || empty($post['title']) || empty($post['name'])) {
            $msg = '缺少必填信息！';
            return false;
        }

        if (1 == preg_match('/^([_]+|[0-9]+)$/', $post['name'])) {
            $msg = '字段名称格式不正确！';
            return false;
        } else if (preg_match('/^rrz_/', $post['name'])) {
            $msg = '字段名称不允许以 rrz_ 开头！';
            return false;
        }
        $fieldType = $this->fieldType;

        // 字段类型是否具备筛选功能
        if ($this->isFilter) {
            if (empty($post['is_filter']) || !$fieldType[$post['dtype']]['is_filter']) {
                $post['is_filter'] = 0;
            }
        }

        //去除中文逗号，过滤左右空格与空值
        $dfvalue = $post['dfvalue'];
        if (in_array($post['dtype'], ['radio', 'checkbox', 'select', 'region'])) {
            $pattern = ['"', '\'', ';', '&', '?', '=', ',', '，'];
            $dfvalue = str_replace($pattern, '', $dfvalue);
        }
        $dfvalueArr = explode('|', $dfvalue);
        foreach ($dfvalueArr as $key => $val) {
            $val = trim($val);
            if (empty($val) && $val !== '0') {
                unset($dfvalueArr[$key]);
                continue;
            }
            $dfvalueArr[$key] = $val;
        }
        $dfvalueArr = array_unique($dfvalueArr);
        $post['dfvalue'] = $dfvalue = implode('|', $dfvalueArr);

        //默认值必填字段
        if (isset($fieldType[$post['dtype']]) && 1 == $fieldType[$post['dtype']]['is_option']) {
            if (empty($dfvalue)) {
                $msg = "你设定了字段为【" . $fieldType[$post['dtype']]['title'] . "】类型，默认值不能为空！ ";
                return false;
            }
        }

        return $post;
    }

    /**
     * 保存表字段
     * @param int $id
     * @param array $post
     * @param array $oldField
     * @param string $msg
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function saveField($id = 0, $post = [], $oldField = [], &$msg = '') {
        $post = $this->checkSaveData($post, $msg);
        if (!$post) return false;
        $fieldType = $this->fieldType;

        $tableName = $this->tableName;
        $table = $this->prefix . $tableName;

        $fieldInfo = $this->GetFieldMake($post['dtype'], $post['name'], $post['dfvalue'], $post['title']);
        if (is_numeric($id) && $id) {//更新
            $old = $oldField;
            if ('checkbox' == $old['dtype'] && in_array($post['dtype'], ['radio', 'select'])) {
                $msg = "{$fieldType['checkbox']['title']}不能更改为{$fieldType[$post['dtype']]['title']}！";
                return false;
            }

            //检测字段是否存在于主表中
            if ($this->checkChannelFieldList($tableName, $post['name'], [$old['name'],])) {
                $msg = "字段名称 " . $post['name'] . " 与系统字段冲突！";
                return false;
            }
            $sql = " ALTER TABLE `{$table}` CHANGE COLUMN `{$old['name']}` {$fieldInfo['sql']} ";
            if (false === Db::execute($sql)) {
                $msg = '操作失败！';
                return false;
            }
            //针对单选项、多选项、下拉框：修改之前，将该字段不存在的值都更新为默认值第一个
            if (in_array($old['dtype'], ['radio', 'select', 'checkbox']) && in_array($post['dtype'], ['radio', 'select', 'checkbox'])) {
                $whereArr = [];
                $dfvalueArr = explode('|', $post['dfvalue']);
                $new_dfvalue = $dfvalueArr[0] ?? '';
                if ($dfvalueArr) {
                    $whereArr[] = [$post['name'], 'not in', $dfvalueArr];
                }
                $whereArr[] = [$post['name'], '=', ''];
                M($tableName)->whereOr($whereArr)->whereOr($post['name'], 'null')->field($post['name'])->update([$post['name'] => $new_dfvalue]);
            }
            try {
                schemaTable($table);
            } catch (\Exception $e) {
            }
            $msg = '操作成功！';
            return $fieldInfo;
        }

        //插入
        //检测字段是否存在于主表中
        if ($this->checkChannelFieldList($tableName, $post['name'])) {
            $msg = "字段名称 " . $post['name'] . " 与系统字段冲突！";
            return false;
        }
        $sql = " ALTER TABLE `{$table}` ADD {$fieldInfo['sql']} ";
        if (false === Db::execute($sql)) {
            $msg = '操作失败！';
            return false;
        }

        try {
            schemaTable($table);
        } catch (\Exception $e) {
        }
        return $fieldInfo;
    }

    /**
     * 删除表字段
     * @param $name
     * @param string $msg
     * @return bool
     */
    public function delField($name = '', &$msg = '') {
        $table = $this->tableName;
        if ($this->checkTableFieldList($table, $name)) {

            $table = $this->prefix . $this->tableName;

            $sql = "ALTER TABLE `{$table}` DROP COLUMN `{$name}`;";
            if (false === Db::execute($sql)) {
                $msg = '删除字段失败！';
                return false;
            }

            //重新生成数据表字段缓存文件
            try {
                schemaTable($table);
            } catch (\Exception $e) {
            }
        }
        return true;
    }


    /**
     * 预处理字段值
     * @param array $fields 字段信息
     * @param array $data 需要处理的数据集
     * @return array
     */
    public function checkFieldValue($fields = [], $data = []) {
        //没有字段
        if (!$fields) return $data;

        //处理字段值
        foreach ($fields as $field) {
            //if (!isset($data[$field['name']])) continue;
            $value = $data[$field['name']] ?? '';//字段值
            $dtype = $field['dtype'];//字段类型
            if (in_array($dtype, ['int', 'float', 'decimal'])) {//数值型
                if (!is_numeric($value) || !$value) {
                    $value = 0;
                }
            } elseif (in_array($dtype, ['checkbox', 'imgs'])) {//数组型
                $value = (is_array($value) && $value) ? implode(',', $value) : '';
            } elseif ($dtype == 'datetime') {//日期型
                $value = $value ? strtotime($value) ?: 0 : 0;
            }
            $data[$field['name']] = $value;
        }
        return $data;
    }

}