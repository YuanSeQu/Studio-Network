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

use \think\facade\Db;

class System extends Base
{
    function index() {
        $this->pagedata['tabs'] = [
            ['name' => '管理员列表',],
        ];
        $this->pagedata['actions'] = [
            ['label' => '添加管理员', 'target' => 'dialog', 'href' => U('System/addAdmin'), 'options' => '{title:"添加管理员",area:["400px"]}',],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'id', 'title' => 'ID', 'width' => '100',],
            ['field' => 'head_pic', 'title' => '', 'width' => '50', 'callback' => function ($item) {
                return '<img width="40" height="40" src="' . ($item['head_pic'] ?: '/static/images/dfboy.png') . '"/>';
            },],
            ['field' => 'user_name', 'title' => '用户名', 'width' => '100', 'align' => 'left',],
            ['field' => 'true_name', 'title' => '真实姓名', 'width' => '100',],
            ['field' => 'mobile', 'title' => '手机号码', 'width' => '150',],
            ['field' => 'last_login', 'title' => '最后登录时间', 'width' => '150', 'type' => 'time',],
            ['field' => 'cz', 'title' => '操作', 'width' => '100', 'callback' => function ($item) {
                $html = '<a href="' . U('System/addAdmin', ['id' => $item['id'],]) . '" options="{title:\'编辑管理员\',area:[\'400px\']}" target="dialog" class="layui-btn layui-btn-xs">编辑</a>';
                if ($item['id'] != 1) {
                    $html .= '<a href="' . U('System/delAdmin', ['id' => $item['id'],]) . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>';
                }
                return $html;
            }],
        ];
        $this->pagedata['model'] = M('admin')->where('status', 1);
        $this->pagedata['fixedColumn'] = true;
        return $this->grid_fetch();
    }

    /**
     * 删除管理员账号
     */
    function delAdmin() {
        $id = I('get.id');
        $rs = M('admin')->where('id', $id)->delete();
        $rs or $this->error('删除失败！');
        $this->success('删除成功！', true);
    }

    /**
     * 添加管理员账号
     */
    function addAdmin() {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            is_numeric($id) and $row = M('admin')->where('id', $id)->find();
            $this->assign('row', $row ?? []);
            return $this->fetch();
        }
        $data = I('post.');
        $data['user_name'] = trim($data['user_name']) or $this->error('请填写账号！');
        $data['password'] = trim($data['password']);
        $data['password2'] = trim($data['password2']);

        if ($data['password'] != $data['password2']) {
            $this->error('两次密码输入不一致！');
        }
        $data['password'] and $data['password'] = md5($data['password']);
        unset($data['password2']);

        $data['update_time'] = time();
        if (is_numeric($id)) {
            $old = M('admin')->where('id', $id)->find();
            if ($old['user_name'] != $data['user_name']) {
                $count = M('admin')->where('user_name', $data['user_name'])->count();
                $count and $this->error('用户名已存在！');
            }

            if (!$data['password']) {
                unset($data['password'], $data['password2']);
            }

            $rs = M('admin')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');

            if ($this->account['id'] == $id && ($old['user_name'] != $data['user_name'] || (isset($data['password']) && $old['password'] != $data['password']))) {
                $this->success('保存成功，当前账号需重新登陆！', ['jump' => U('Login/logout')]);
            }
            $this->success('保存成功！', true);
        }
        $data['role_id'] = 1;
        $data['add_time'] = time();
        $count = M('admin')->where('user_name', $data['user_name'])->count();
        $count and $this->error('用户名已存在！');

        $rs = M('admin')->insert($data);
        $rs or $this->error('保存失败！');
        $this->success('保存成功！', true);
    }

    /**
     * 清除缓存
     */
    function clearCache() {
        if (!$this->request->isPost()) {
            return $this->fetch();
        }
        if (!function_exists('unlink')) {
            $this->error('php.ini未开启unlink函数，请联系空间商处理！');
        }

        $clear = I('post.clearCache', []);
        clearCache($clear);

        $this->success('操作成功');

    }

    /**
     * 数据备份
     */
    function backup() {
        $this->pagedata['tabs'] = [
            ['name' => '数据备份', 'class' => 'current',],
            ['name' => '数据还原', 'url' => U('System/restore'),],
        ];

        $this->pagedata['actions'] = [
            ['label' => '数据备份', 'class' => 'js-backup',],
        ];
        $this->pagedata['columns'] = [
            ['field' => 'Name', 'title' => '数据库表', 'width' => '250', 'align' => 'left',],
            ['field' => 'Rows', 'title' => '记录条数', 'width' => '90',],
            ['field' => 'data_size', 'title' => '占用空间', 'width' => '90',],
            ['field' => 'Collation', 'title' => '编码', 'width' => '150',],
            ['field' => 'Create_time', 'title' => '创建时间', 'width' => '150',],
            ['field' => 'zt', 'title' => '备份状态', 'width' => '90', 'callback' => function ($row) {
                return '未备份';
            }],
            ['field' => 'cz', 'title' => '操作', 'width' => '100', 'callback' => function ($row) {
                return '<a href="' . U('System/repair') . '?name=' . urlencode($row['Name']) . '" target="ajax" class="layui-btn layui-btn-xs">修复</a>';
            }],
        ];
        $dbtables = Db::query('SHOW TABLE STATUS');
        $config = Db::getConnection()->getConfig();
        $prefix = $config['prefix'];

        $total = 0;
        $list = [];
        foreach ($dbtables as $k => $v) {
            if (preg_match('/^' . $prefix . '/i', $v['Name'])) {
                $v['data_size'] = format_bytes($v['Data_length']);
                $v['size'] = format_bytes($v['Data_length'] + $v['Index_length']);
                $list[$k] = $v;
                $total += $v['Data_length'] + $v['Index_length'];
            }
        }
        $this->pagedata['actions'][] = '<span class="cl-999 f12 ml10 vb">共' . count($list) . '条数据，共计' . format_bytes($total) . '</span>';

        $this->pagedata['data'] = $list;

        $this->pagedata['pk_field'] = 'Name';
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['checkType'] = 'checkbox';
        $this->pagedata['isPage'] = false;
        $this->pagedata['grid_class'] = 'js-view-backup';

        return $this->grid_fetch();
    }

    function repair() {
        $table = I('name') or $this->error('参数错误');
        $strTable = is_array($table) ? implode(',', $table) : $table;
        Db::query("REPAIR TABLE {$strTable} ");
        $this->success('操作成功', true);
    }

    function doBackup() {
        $tables = I('post.tables', null);
        $id = I('post.id', null);
        $start = I('post.start', null);
        $optstep = I('post.optstep', null);

        $this->request->isPost() or $this->error('参数有误!');

        if ('all' == $tables) {
            $dbtables = Db::query('SHOW TABLE STATUS');
            $prefix = Db::getConnection()->getConfig('prefix');
            $list = [];
            foreach ($dbtables as $k => $v) {
                if (preg_match('/^' . $prefix . '/i', $v['Name'])) {
                    $list[] = $v['Name'];
                }
            }
            $tables = $list;
        }

        //防止备份数据过程超时
        function_exists('set_time_limit') && set_time_limit(0);
        @ini_set('memory_limit', '-1');

        if (!empty($tables) && is_array($tables) && empty($optstep)) {//初始化
            $path = sysConfig('admin.sqldatapath');
            $path = !empty($path) ? $path : C('config.DATA_BACKUP_PATH');
            $path = trim($path, '/');
            if (!empty($path) && !is_dir($path)) {
                mkdir($path, 0755, true);
            }
            //读取备份配置
            $config = [
                'path' => realpath($path) . DIRECTORY_SEPARATOR,
                'part' => C('config.DATA_BACKUP_PART_SIZE'),
                'compress' => C('config.DATA_BACKUP_COMPRESS'),
                'level' => C('config.DATA_BACKUP_COMPRESS_LEVEL'),
            ];
            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            if (is_file($lock)) {
                return $this->error('检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                //创建锁文件
                file_put_contents($lock, $_SERVER['REQUEST_TIME']);
            }

            //检查备份目录是否可写
            if (!is_writeable($config['path'])) {
                return $this->error('备份目录不存在或不可写，请检查后重试！');
            }
            session('backup_config', $config);
            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $_SERVER['REQUEST_TIME']),
                'part' => 1,
                'version' => C('config.sys_version'),
            ];
            session('backup_file', $file);
            //缓存要备份的表
            session('backup_tables', $tables);
            //创建备份文件
            $Database = new \app\admin\lib\Backup($file, $config);
            if (false === $Database->create()) {
                return $this->error('初始化失败，备份文件创建失败！');
            }
            $speed = (floor((1 / count($tables)) * 10000) / 10000 * 100);
            $speed = sprintf("%.2f", $speed);
            $tab = ['id' => 0, 'start' => 0, 'speed' => $speed, 'table' => $tables[0], 'optstep' => 1];
            return $this->success('初始化成功！', ['tables' => $tables, 'tab' => $tab,]);

        } elseif (is_numeric($id) && is_numeric($start) && 1 == intval($optstep)) {
            $tables = session('backup_tables');
            $file = session('backup_file');
            $config = session('backup_config');

            //备份指定表
            $Database = new \app\admin\lib\Backup($file, $config);
            $start = $Database->backup($tables[$id], $start);
            if (false === $start) { //出错
                return $this->error('备份出错！');
            } elseif (0 === $start) {

                if (isset($tables[++$id])) {
                    $speed = (floor((($id + 1) / count($tables)) * 10000) / 10000 * 100);
                    $speed = sprintf("%.2f", $speed);
                    $tab = ['id' => $id, 'start' => 0, 'speed' => $speed, 'table' => $tables[$id], 'optstep' => 1,];
                    return $this->success('备份完成！', ['tab' => $tab,]);
                } else { //备份完成，清空缓存
                    /*自动覆盖安装的rrzcms.sql*/
                    $install_path = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'setup' . DIRECTORY_SEPARATOR;
                    if (is_dir($install_path) && file_exists($install_path)) {
                        $srcfile = $config['path'] . $file['name'] . '-' . $file['part'] . '-' . $file['version'] . '.sql';
                        $dstfile = $install_path . '/rrzcms.sql';
                        @unlink($dstfile);// 复制失败就删掉，避免安装错误的数据包
                        if (@copy($srcfile, $dstfile)) {
                            $rrzDbStr = file_get_contents($dstfile);
                            $dbtables = Db::query('SHOW TABLE STATUS');
                            $prefix = Db::getConnection()->getConfig('prefix');
                            $tableName = $eyTableName = [];
                            foreach ($dbtables as $k => $v) {
                                if (preg_match('/^' . $prefix . '/i', $v['Name'])) {
                                    $tableName[] = "`{$v['Name']}`";
                                    $eyTableName[] = preg_replace('/^`' . $prefix . '/i', '`rrz_', "`{$v['Name']}`");
                                }
                            }
                            $rrzDbStr = str_replace($tableName, $eyTableName, $rrzDbStr);
                            @file_put_contents($dstfile, $rrzDbStr);
                            unset($rrzDbStr, $dbtables);
                        }
                    }
                    unlink($config['path'] . 'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    return $this->success('备份完成！');
                }

            } else {
                $rate = floor(100 * ($start[0] / $start[1]));
                $speed = floor((($id + 1) / count($tables)) * 10000) / 10000 * 100 + ($rate / 100);
                $speed = sprintf("%.2f", $speed);
                $tab = ['id' => $id, 'start' => $start[0], 'speed' => $speed, 'table' => $tables[$id], 'optstep' => 1,];
                return $this->success("正在备份...({$rate}%)", ['tab' => $tab,]);
            }


        } else {
            return $this->error('参数有误！');
        }

    }

    /**
     * 数据还原
     */
    function restore() {
        $this->pagedata['tabs'] = [
            ['name' => '数据备份', 'url' => U('System/backup'),],
            ['name' => '数据还原', 'class' => 'current',],
        ];
        $this->pagedata['columns'] = [
            ['field' => '', 'title' => '', 'width' => '20',],
            ['field' => 'basename', 'title' => '文件名称', 'width' => '250', 'align' => 'left',],
            ['field' => 'version', 'title' => '系统版本', 'width' => '90',],
            ['field' => 'part', 'title' => '卷号', 'width' => '70',],
            ['field' => 'compress', 'title' => '压缩', 'width' => '90',],
            ['field' => 'size', 'title' => '数据大小', 'width' => '90', 'callback' => function ($row) {
                return format_bytes($row['size']);
            }],
            ['field' => 'time', 'title' => '备份时间', 'width' => '150', 'type' => 'time',],
            ['field' => 'cz', 'title' => '操作', 'width' => '160', 'callback' => function ($row) {
                $html = '<a href="' . U('System/doRestore') . '?time=' . $row['time'] . '" msg="确定要恢复数据吗？" target="confirm" class="layui-btn layui-btn-xs layui-btn-primary">恢复</a>';
                $html .= '<a href="' . U('System/downSqlFile') . '?time=' . $row['time'] . '" target="_blank" class="layui-btn layui-btn-xs">下载</a>';
                $html .= '<a href="' . U('System/delSqlFile') . '?time=' . $row['time'] . '" msg="确定要删除吗？" target="confirm" class="layui-btn layui-btn-xs layui-btn-danger">删除</a>';
                return $html;
            }],
        ];
        $path = sysConfig('admin.sqldatapath');
        $path = !empty($path) ? $path : C('config.DATA_BACKUP_PATH');
        $path = trim($path, '/');
        if (!empty($path) && !is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $path = realpath($path);
        $glob = new \FilesystemIterator($path, \FilesystemIterator::KEY_AS_FILENAME);
        $list = array();
        $filenum = $total = 0;
        foreach ($glob as $name => $file) {
            if (preg_match('/^\d{8,8}-\d{6,6}-\d+-v\d+\.\d+\.\d+(.*)\.sql(?:\.gz)?$/', $name)) {
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d-%s');
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];
                $version = preg_replace('#\.sql(.*)#i', '', $name[7]);
                $info = pathinfo($file);
                $time = strtotime("{$date} {$time}");
                if (isset($list[$time])) {
                    $info = $list[$time];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                } else {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }
                $info['compress'] = ($info['extension'] === 'sql') ? '-' : $info['extension'];
                $info['time'] = $time;
                $info['version'] = $version;
                $filenum++;
                $total += $info['size'];
                $list[$time] = $info;
            }
        }
        array_multisort($list, SORT_DESC);
        $this->pagedata['actions'][] = 'sql文件列表<span class="cl-999 f12 ml10 vb">(备份文件数量：' . $filenum . '，占空间大小：' . format_bytes($total) . ')</span>';
        $this->pagedata['data'] = array_values($list);

        $this->pagedata['pk_field'] = 'basename';
        $this->pagedata['fixedColumn'] = true;
        //$this->pagedata['checkType'] = 'checkbox';
        $this->pagedata['isPage'] = false;
        $this->pagedata['grid_class'] = 'js-view-restore';

        return $this->grid_fetch();
    }

    function doRestore($time = 0) {
        function_exists('set_time_limit') && set_time_limit(0);
        @ini_set('memory_limit', '-1');

        if (!is_numeric($time) || intval($time) <= 0) {
            $this->error('参数有误');
        }
        //获取备份文件信息
        $name = date('Ymd-His', $time) . '-*.sql*';
        $path = sysConfig('admin.sqldatapath');
        $path = !empty($path) ? $path : C('config.DATA_BACKUP_PATH');
        $path = trim($path, '/');
        $path = realpath($path) . DIRECTORY_SEPARATOR . $name;
        $files = glob($path);
        $list = [];
        foreach ($files as $name) {
            $basename = basename($name);
            $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d-%s');
            $gz = preg_match('/^\d{8,8}-\d{6,6}-\d+-v\d+\.\d+\.\d+(.*)\.sql.gz$/', $basename);
            $list[$match[6]] = [$match[6], $name, $gz];
        }
        ksort($list);

        //检测文件正确性
        $last = end($list);
        $file_path_full = !empty($last[1]) ? $last[1] : '';
        if (file_exists($file_path_full)) {
            /*校验sql文件是否属于当前CMS版本*/
            preg_match('/(\d{8,8})-(\d{6,6})-(\d+)-(v\d+\.\d+\.\d+(.*))\.sql/i', $file_path_full, $matches);
            $version = C('config.sys_version');
            if ($matches[4] != $version) {
                $this->error('sql不兼容当前版本：' . $version, true);
            }

            $sqls = \app\admin\lib\Backup::parseSql($file_path_full);
            \app\admin\lib\Backup::install($sqls) or $this->error('操作失败！', true);
            clearCache(true);

            $this->success('操作成功', '', ['jump' => getAppRootUrl()]);
        }
        $this->error('备份文件可能已经损坏，请检查！');
    }

    function downSqlFile($time = 0) {
        $name = date('Ymd-His', $time) . '-*.sql*';
        $path = sysConfig('admin.sqldatapath');
        $path = !empty($path) ? $path : C('config.DATA_BACKUP_PATH');
        $path = trim($path, '/');
        $path = realpath($path) . DIRECTORY_SEPARATOR . $name;

        $files = glob($path);
        if (!is_array($files) || !$files) {
            $this->error('文件不存在，可能是被删除，请检查！');
        }
        foreach ($files as $filePath) {
            if (!file_exists($filePath)) {
                $this->error("该文件不存在，可能是被删除");
            } else {
                $filename = basename($filePath);
                header("Content-type: application/octet-stream");
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header("Content-Length: " . filesize($filePath));
                readfile($filePath);
            }
        }
        exit;
    }

    function delSqlFile($time = 0) {
        $times = is_array($time) ? $time : (is_numeric($time) ? [$time] : false);
        if (!$times || !is_array($times)) $this->error('参数有误');

        $path = sysConfig('admin.sqldatapath');
        $path = !empty($path) ? $path : C('config.DATA_BACKUP_PATH');
        $path = trim($path, '/');

        foreach ($times as $time) {
            $name = date('Ymd-His', $time) . '-*.sql*';
            $path = realpath($path) . DIRECTORY_SEPARATOR . $name;
            array_map('unlink', glob($path));
            if (count(glob($path))) {
                $this->error('备份文件删除失败，请检查目录权限！');
            }
        }
        $this->success('删除成功！', true);
    }

}