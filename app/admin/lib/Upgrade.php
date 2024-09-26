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

class Upgrade
{
    public $dataPath;
    public $rootPath;
    public $curentVersion;
    public $upgradeUrl;

    function __construct() {
        $this->rootPath = app()->getRootPath();
        $this->dataPath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
        $config = include $this->rootPath . '/app/admin/config/config.php';
        $this->curentVersion = $config['sys_version'];
        $url = base64_decode('aHR0cDovL3d3dy5ycnpjbXMuY29tL0FwaS9ScnpjbXMvY2hlY2tWZXJzaW9u');
        $md5 = sysConfig('system.upgrade_md5') ?: '';
        $this->upgradeUrl = $url . '?domain=' . request()->host(true) . '&v=' . $this->curentVersion . '&md5=' . $md5;
    }

    //检查是否有更新包
    function checkVersion(&$msg = '') {
        $url = $this->upgradeUrl;
        $res = get_curl($url, 'json');
        if (empty($res) || !$res['data']) {
            $msg = '已是最新版';
            return false;
        }
        $upgrade = end($res['data']);//最新版本
        $tips = [];
        foreach ($res['data'] as $item) {
            $tips[] = '<p class="f15 cl-000">版本 ' . $item['title'] . '&nbsp;<span class="f12 cl-999 pr" style="top:-1px;">(' . $item['uptime'] . ')</span>' . '</p>' . trim(htmlspecialchars_decode($item['content']));
        }
        $upgrade['upTipsMsg'] = '<font color="red">小提示：系统更新不会涉及前台模板及网站数据等。</font><br>更新日志：<br>' . implode('', array_reverse($tips));
        return $upgrade;
    }

    //一键更新
    function OneKeyUpgrade(&$msg = '') {
        ignore_user_abort(true);//忽略断开
        error_reporting(0);//关闭所有错误报告
        if (!extension_loaded('zip')) {
            $msg = '请联系空间商，开启 php.ini 中的php-zip扩展';
            return false;
        }
        $res = get_curl($this->upgradeUrl, 'json');
        if (!$res) {
            $msg = '无法连接远程升级服务器！';
            return false;
        }
        if (empty($res['data'])) {
            $msg = '当前没有可升级的版本！';
            return false;
        }
        $versionList = $res['data'];
        clearstatcache(); // 清除文件夹权限缓存
        $lastVersion = end($versionList);

        $folderName = $lastVersion['title'];
        $newFilePath = $this->dataPath . 'upgrade' . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;
        delFile($newFilePath, true);
        foreach ($versionList as $key => $val) {
            //下载更新包
            $fileZip = $this->downloadFile($val['file_url'], $val['file_md5'], $msg);
            if (!$fileZip) {
                return false;
            }
            /*解压文件*/
            $zip = new \ZipArchive();//新建一个ZipArchive的对象
            if ($zip->open($fileZip) != true) {
                $msg = '升级包读取失败！';
                return false;
            }
            $zip->extractTo($newFilePath);//解压缩
            $zip->close();//关闭处理的zip文件
        }

        $upgradeArr = $this->getFileList($newFilePath . DIRECTORY_SEPARATOR . 'rrzcms');

        clearstatcache();//清除状态缓存
        /*升级之前，备份涉及的源文件*/
        foreach ($upgradeArr as $val) {
            $val = str_replace($newFilePath . DIRECTORY_SEPARATOR . 'rrzcms/', '', $val);
            $source_file = $this->rootPath . $val;
            if (file_exists($source_file)) {
                if (!is_writable($source_file) || !is_readable($source_file)) {
                    $msg = '升级失败，文件【' . $source_file . '】没有读写权限！';
                    return false;
                }
                $destination_file = $this->dataPath . 'backup' . DIRECTORY_SEPARATOR . $this->curentVersion . '_www' . DIRECTORY_SEPARATOR . $val;
                $path = dirname($destination_file);
                is_dir($path) or mkdir($path, 0755, true);
                $copy_bool = @copy($source_file, $destination_file);
                if (false == $copy_bool) {
                    $msg = '更新前备份文件失败，请检查所有目录是否有读写权限';
                    return false;
                }
            }
        }
        $dbConfig = Db::getConnection()->getConfig();
        $prefix = $dbConfig['prefix'];
        //执行数据库更新
        foreach ($versionList as $key => $val) {
            $sqlFile = $newFilePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $val['title'] . '.sql';
            if (!is_file($sqlFile)) continue;
            $sql = file_get_contents($sqlFile);
            $sqlFormat = $this->sql_split($sql, $prefix);
            //执行SQL语句
            $counts = count($sqlFormat);
            for ($i = 0; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (!$sql) continue;
                try {
                    Db::execute($sql);
                } catch (\Throwable | \Exception $e) {
                    $msg = $e->getMessage();
                    //重复的字段和索引错误不提示继续执行。
                    if (!preg_match('/Duplicate (column|key) name/i', $msg)) {
                        $msg = '数据库执行中途失败，请查联系官方解决，否则将影响后续的版本升级！';
                        return false;
                    }
                }
            }
        }
        $failure_count = 0;//累计覆盖失败的文件总数
        $success_count = 0;//累计执行覆盖的文件总数
        //覆盖升级文件
        foreach ($upgradeArr as $val) {
            if (strpos($val, '/.env')) {
                $strConfig = file_get_contents($val);
                $replace = [
                    '#HOSTNAME#' => $dbConfig['hostname'],
                    '#DATABASE#' => $dbConfig['database'],
                    '#USERNAME#' => $dbConfig['username'],
                    '#PASSWORD#' => $dbConfig['password'],
                    '#HOSTPORT#' => $dbConfig['hostport'],
                    '#PREFIX#' => $dbConfig['prefix'],
                    '#CHARSET#' => 'utf8mb4',
                    '#ADMIN_MAP#' => sysConfig('admin.app_map') ?: 'admin',
                ];
                $strConfig = str_replace(array_keys($replace), array_values($replace), $strConfig);
                file_put_contents($val, $strConfig);
            }
            $dest = str_replace($newFilePath . DIRECTORY_SEPARATOR . 'rrzcms/', '', $val);
            $dest = $this->rootPath . $dest;
            $path = dirname($dest);
            is_dir($path) or mkdir($path, 0755, true);
            $rs = @copy($val, $dest);
            if ($rs) {
                $success_count++;
                @unlink($val);
            } else {
                $failure_count++;
            }
        }

        clearCache(true);//清除缓存

        /*删除下载的升级包*/
        $ziplist = glob($this->dataPath . 'upgrade' . DIRECTORY_SEPARATOR . '*.zip');
        @array_map('unlink', $ziplist);
        /*--end*/

        //推送回服务器  记录升级成功
        $this->UpgradeLog($lastVersion['title']);
        sysConfig('system.upgrade_md5', $lastVersion['file_md5']);//记录升级包的md5值
        $msg = '升级成功！';
        if ($failure_count > 0) {
            $msg .= "，其中失败 <font color='red'>{$failure_count}</font> 个文件，<br />请从升级包目录[<font color='red'>runtime/data/upgrade/{$folderName}/rrzcms</font>]中的取出全部文件覆盖到根目录，完成手工升级。";
            return false;
        }
        delFile($newFilePath, true);//删除升级包文件夹
        return true;
    }

    //升级记录 log 日志
    private function UpgradeLog($to_key_num) {
        $mysqlinfo = Db::query("SELECT VERSION() as version");
        $mysql_version = $mysqlinfo[0]['version'];
        $data = [
            'domain' => $_SERVER['HTTP_HOST'], //用户域名
            'key_num' => $this->curentVersion, // 用户版本号
            'to_key_num' => $to_key_num, // 用户要升级的版本号
            'add_time' => time(), // 升级时间
            'ip' => gethostbyname($_SERVER['SERVER_NAME']),
            'phpv' => phpversion(),
            'mysql_version' => $mysql_version,
            'web_server' => $_SERVER['SERVER_SOFTWARE'],
        ];
        $url = base64_decode('aHR0cDovL3d3dy5ycnpjbXMuY29tL0FwaS9ScnpjbXMvdXBncmFkZUxvZw==');
        @post_curl($url, $data);
    }

    //处理语句
    private function sql_split($sql, $tablepre) {
        if ($tablepre != "rrz_")
            $sql = str_replace("`rrz_", '`' . $tablepre, $sql);

        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8mb4", $sql);

        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num++;
        }
        return $ret;
    }

    //获取文件夹内部文件列表
    private function getFileList($path) {
        if (!is_dir($path)) return [];
        $dir = opendir($path);
        $list = [];
        while (false !== ($file = readdir($dir))) {
            if (in_array($file, ['.', '..'])) continue;
            if (is_dir($path . '/' . $file)) {
                $_list = $this->getFileList($path . '/' . $file);
                $list = array_merge($list, $_list);
            } else {
                $list[] = $path . '/' . $file;
            }
        }
        closedir($dir);
        return $list;
    }

    //下载文件
    private function downloadFile($fileUrl, $md5File, &$msg = '') {
        if (!$fileUrl || !$md5File) {
            $msg = '官方升级包不存在';
            return false; // 文件存在直接退出
        }
        $downFileName = explode('/', $fileUrl);
        $downFileName = end($downFileName);
        $saveDir = $this->dataPath . 'upgrade' . DIRECTORY_SEPARATOR . $downFileName; // 保存目录
        $path = dirname($saveDir);
        is_dir($path) or mkdir($path, 0755, true);
        $content = @file_get_contents($fileUrl, 0, null, 0, 1);
        if (!$content) {
            $msg = '官方升级包不存在';
            return false; // 文件存在直接退出
        }
        $file = curl($fileUrl);
        if (preg_match('#__HALT_COMPILER()#i', $file)) {
            $msg = '下载包损坏，请联系官方客服！';
            return false;
        }
        $fp = fopen($saveDir, 'w');
        fwrite($fp, $file);
        fclose($fp);
        if (!preventShell($saveDir) || !file_exists($saveDir) || $md5File != md5_file($saveDir)) {
            $msg = '下载保存升级包失败，请检查所有目录的权限以及用户组不能为root';
            return false;
        }
        return $saveDir;
    }


}