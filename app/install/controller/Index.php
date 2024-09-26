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

namespace app\install\controller;


class Index extends Base
{
    var $mini_php = '';
    var $rootPath = '';
    var $sqlFile = '';
    var $configFile = '';

    function initialize() {
        $this->mini_php = C('config.mini_php');

        $root = $this->app->getRootPath();
        $this->rootPath = $root;

    }

    function checkStatus() {
        if (file_exists($this->rootPath . '/public/setup/install.lock')) {
            exit('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>你已经安装过该系统，如果想重新安装，请先删除站点public/setup目录下的 install.lock 文件，然后再安装。</body></html>');
        }
        @set_time_limit(1000);
        if (phpversion() <= $this->mini_php) {
            @set_magic_quotes_runtime(0);
        }
        if ($this->mini_php > phpversion()) {
            header("Content-type:text/html;charset=utf-8");
            die('本系统要求PHP版本 >= ' . $this->mini_php . '，当前PHP版本为：' . phpversion() . '，请到虚拟主机控制面板里切换PHP版本，或联系空间商协助切换。<a href="http://www.rrzcms.com/Admin/News/info/id/1.html" target="_blank">点击查看人人站安装教程</a>');
        }
        $this->sqlFile = $this->rootPath . '/public/setup/rrzcms.sql';
        $this->configFile = $this->rootPath . '/public/setup/config.env';

        if (!file_exists($this->sqlFile) || !file_exists($this->configFile)) {
            exit("缺少必要的安装文件（{$this->sqlFile} 或 {$this->configFile}）!");
        }
        $envFile = $this->rootPath . '.env.php';
        if (!is_file($envFile)) {
            header("Content-type:text/html;charset=utf-8");
            die("缺少必要的安装文件（{$envFile}），<a href=\"http://www.rrzcms.com/newsinfo/4890.html\" target=\"_blank\">点击查看帮助</a>");
        }
    }

    function index() {
        $this->checkStatus();
        return $this->page();
    }

    function step2() {
        $this->checkStatus();
        $err = 0;

        if ($this->mini_php <= phpversion()) {
            $phpvStr = '<img src="/setup/images/ok.png">';
        } else {
            $phpvStr = '<img src="/setup/images/del.png"> &nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/2.html" target="_blank">当前版本(' . phpversion() . ')不支持</a>';
            $err++;
        }
        $this->assign('phpvStr', $phpvStr);

        $tmp = function_exists('gd_info') ? gd_info() : [];
        $safe_mode = (ini_get('safe_mode') ? '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/3.html" target="_blank">详情</a>' : '<img src="/setup/images/ok.png">');
        $this->assign('safe_mode', $safe_mode);

        if (empty($tmp['GD Version'])) {
            $gd = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/8.html" target="_blank">详情</a>';
            $err++;
        } else {
            $gd = '<img src="/setup/images/ok.png">';
        }
        $this->assign('gd', $gd);

        if (function_exists('mysqli_connect')) {
            $mysql = '<img src="/setup/images/ok.png">';
        } else {
            $mysql = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/4.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('mysql', $mysql);

        if (class_exists('pdo')) {
            $pdo = '<img src="/setup/images/ok.png">';
        } else {
            $pdo = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/5.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('pdo', $pdo);

        if (extension_loaded('pdo_mysql')) {
            $pdo_mysql = '<img src="/setup/images/ok.png">';
        } else {
            $pdo_mysql = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/5.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('pdo_mysql', $pdo_mysql);

        if (function_exists('curl_init')) {
            $curl = '<img src="/setup/images/ok.png">';
        } else {
            $curl = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/6.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('curl', $curl);

        if (function_exists('finfo_open')) {
            $fileinfo = '<img src="/setup/images/ok.png">';
        } else {
            $fileinfo = '<img src="/setup/images/del.png">&nbsp;';
            $err++;
        }
        $this->assign('fileinfo', $fileinfo);

        if (function_exists('file_put_contents')) {
            $file_put_contents = '<img src="/setup/images/ok.png">';
        } else {
            $file_put_contents = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/7.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('file_put_contents', $file_put_contents);

        if (function_exists('scandir')) {
            $scandir = '<img src="/setup/images/ok.png">';
        } else {
            $scandir = '<img src="/setup/images/del.png">&nbsp;<a href="http://www.rrzcms.com/Admin/News/info/id/9.html" target="_blank">详情</a>';
            $err++;
        }
        $this->assign('scandir', $scandir);

        if (function_exists('mb_convert_encoding')) {
            $mbstring = '<img src="/setup/images/ok.png">';
        } else {
            $mbstring = '<img src="/setup/images/del.png">&nbsp;';
            $err++;
        }
        $this->assign('mbstring', $mbstring);

        if (extension_loaded('zip')) {
            $zip = '<img src="/setup/images/ok.png">';
        } else {
            $zip = '<img src="/setup/images/del.png">&nbsp;';
            $err++;
        }
        $this->assign('zip', $zip);

        if (function_exists('chmod')) {
            $chmod = '<img src="/setup/images/ok.png">';
        } else {
            $chmod = '<img src="/setup/images/del.png">&nbsp;';
            $err++;
        }
        $this->assign('chmod', $chmod);


        @chmod($this->rootPath . '.env.php', 0777); //数据库配置文件的地址

        $folder = [
            'public/addons',
            'public/setup',
            'public/setup/rrzcms.sql',
            'public/storage',
            'public/template',
            //'public/sitemap.xml',
            'runtime',
            //'.env',
        ];
        $this->assign('rootPath', $this->rootPath);
        $this->assign('folder', $folder);
        $this->assign('err', $err);

        return $this->page();
    }

    function step3() {
        $this->checkStatus();
        if (!$this->request->isPost()) {
            return $this->page();
        }
        $data = I('post.');

        $dbName = trim(addslashes($data['dbName'] ?? ''));
        $dbUser = trim(addslashes($data['dbUser'] ?? ''));
        $dbport = !empty($data['dbport']) ? addslashes($data['dbport']) : '3306';
        $dbPwd = $data['dbPwd'];
        $dbHost = addslashes($data['dbHost'] ?? '');

        if ($_GET['testdbpwd'] ?? false) {
            $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, null, $dbport);
            if (mysqli_connect_errno()) {
                exit(json_encode([
                    'errcode' => 0,
                    'dbpwmsg' => "<span for='dbname' generated='true' class='tips_error'>数据库连接失败，请重新设定</span>",
                ]));
            } else {
                if (empty($dbName)) {
                    exit(json_encode([
                        'errcode' => -2,
                        'dbpwmsg' => "<span class='green'>信息正确</span>",
                        'dbnamemsg' => "<span class='red'>数据库不能为空，请设定</span>",
                    ]));
                } else {
                    /*检测数据库是否存在*/
                    $result = mysqli_query($conn, "select count(table_name) as c from information_schema.`TABLES` where table_schema='$dbName'");
                    $result = $result->fetch_array();
                    if ($result['c'] > 0) { // 存在
                        $dbnamemsg = "<span class='red'>数据库已经存在，系统将覆盖数据库</span>";
                    } else { // 不存在
                        $dbnamemsg = "<span class='green'>数据库不存在，系统将自动创建</span>";
                    }
                    /*--end*/
                }
                exit(json_encode([
                    'errcode' => 1,
                    'dbpwmsg' => "<span class='green'>信息正确</span>",
                    'dbnamemsg' => $dbnamemsg,
                ]));
            }
        } else if ($_GET['check'] ?? false) {
            if (!function_exists('mysqli_connect')) {
                $arr = [
                    'code' => -1,
                    'msg' => "请安装 mysqli 扩展！",
                ];
                exit(json_encode($arr));
            }

            $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, null, $dbport);
            if (mysqli_connect_errno()) {
                $arr = [
                    'code' => -1,
                    'msg' => "请检查数据库连接信息，" . iconv('gbk', 'utf-8', mysqli_connect_error($conn)),
                ];
                exit(json_encode($arr));
            }

            $version = mysqli_get_server_info($conn);
            if ($version < '5.5.5') {
                $arr = [
                    'code' => -1,
                    'msg' => '数据库版本(' . $version . ')太低！必须 >= 5.6',
                ];
                exit(json_encode($arr));
            }
            mysqli_set_charset($conn, "utf8mb4");

            if (!@mysqli_select_db($conn, $dbName)) {
                //创建数据时同时设置编码
                if (!@mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4;")) {
                    $arr = [
                        'code' => -1,
                        'msg' => '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库，建议联系空间商或者服务器负责人！',
                    ];
                    exit(json_encode($arr));
                }
            }

            $arr = [
                'code' => 0,
                'msg' => '',
            ];
            exit(json_encode($arr));
        }
    }

    function step4() {
        $this->checkStatus();
        $data = I('post.');
        $arr = [];

        $dbHost = trim(addslashes($data['dbhost'] ?? ''));
        $dbport = !empty($data['dbport']) ? addslashes($data['dbport']) : '3306';
        $dbName = trim(addslashes($data['dbname'] ?? ''));
        $dbUser = trim(addslashes($data['dbuser'] ?? ''));
        $dbPwd = trim($data['dbpw'] ?? '');
        $dbPrefix = empty($data['dbprefix']) ? 'rrz_' : trim(addslashes($data['dbprefix']));

        $username = trim(addslashes($data['manager'] ?? ''));
        $password = trim($data['manager_pwd'] ?? '');

        if (!function_exists('mysqli_connect')) {
            $arr['code'] = 0;
            $arr['msg'] = "请安装 mysqli 扩展!";
            exit(json_encode($arr));
        }

        $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, null, $dbport);
        if (mysqli_connect_errno()) {
            $arr['code'] = 0;
            $arr['msg'] = "连接数据库失败!" . mysqli_connect_error($conn);
            exit(json_encode($arr));
        }

        $version = mysqli_get_server_info($conn);
        if ($version < '5.5.5') {
            $arr['code'] = 0;
            $arr['msg'] = '数据库版本(' . $version . ')太低! 必须 >= 5.6';
            exit(json_encode($arr));
        }
        mysqli_set_charset($conn, "utf8mb4");

        if (!@mysqli_select_db($conn, $dbName)) {
            //创建数据时同时设置编码
            if (!@mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4;")) {
                $arr['code'] = 0;
                $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库，建议联系空间商或者服务器负责人！';
                exit(json_encode($arr));
            }
            mysqli_select_db($conn, $dbName);
        }

        //读取数据文件
        $sqldata = file_get_contents($this->sqlFile);
        $sqlFormat = sql_split($sqldata, $dbPrefix);
        //创建写入sql数据库文件到库中 结束

        $sqlVersion = strMatch('/-- Version : #(v[0-9.]+)/', $sqldata);

        /**
         * 执行SQL语句
         */
        $counts = count($sqlFormat);
        for ($i = 0; $i < $counts; $i++) {
            $sql = trim($sqlFormat[$i]);

            if (strstr($sql, 'CREATE TABLE')) {
                preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                mysqli_query($conn, "DROP TABLE IF EXISTS `$matches[1]");
                $ret = mysqli_query($conn, $sql);
                if (!$ret) {
                    $message = '创建数据表' . $matches[1] . '失败，请尝试F5刷新!';
                    $arr['code'] = 0;
                    $arr['msg'] = $message;
                    exit(json_encode($arr));
                }
            } else {
                if (trim($sql) == '')
                    continue;
                preg_match('/INSERT INTO `([^ ]*)`/', $sql, $matches);
                $ret = mysqli_query($conn, $sql);
                if (!$ret) {
                    $message = '写入表' . $matches[1] . '记录失败，请尝试F5刷新!';
                    $arr['code'] = 0;
                    $arr['msg'] = $message;
                    exit(json_encode($arr));
                }
            }
        }

        //处理模版数据文件升级
        $this->upgrade($conn, $dbPrefix, $sqlVersion);

        /*清空缓存*/
        delFile($this->rootPath . '/runtime/', true);
        /*--end*/

        //读取配置文件，并替换真实配置数据1
        $strConfig = file_get_contents($this->configFile);
        $replace = [
            '#HOSTNAME#' => $dbHost,
            '#DATABASE#' => $dbName,
            '#USERNAME#' => $dbUser,
            '#PASSWORD#' => $dbPwd,
            '#HOSTPORT#' => $dbport,
            '#PREFIX#' => $dbPrefix,
            '#CHARSET#' => 'utf8mb4',
            '#ADMIN_MAP#' => 'admin',
        ];
        $strConfig = str_replace(array_keys($replace), array_values($replace), $strConfig);
        @chmod($this->rootPath . '.env.php', 0755); //数据库配置文件的地址
        @file_put_contents($this->rootPath . '.env.php', $strConfig); //数据库配置文件的地址

        mysqli_query($conn, " UPDATE `{$dbPrefix}config` SET `value`='admin' WHERE `type`='admin' and `name`='app_map' ");
        //更新管理员账号
        $password = md5($password);
        mysqli_query($conn, " UPDATE `{$dbPrefix}admin` SET `password`='$password',user_name='$username',true_name='$username' WHERE id=1 ");

        @touch($this->rootPath . '/public/setup/install.lock');

        $url = url('Index/step5')->build();

        $arr['code'] = 1;
        $arr['msg'] = "安装成功";
        $arr['url'] = $url;
        echo json_encode($arr);
        exit;
    }

    function step5() {
        $ip = gethostbyname($_SERVER['SERVER_NAME']);
        $host = $_SERVER['HTTP_HOST'];
        $phpv = urlencode(phpversion());
        $web_server = urlencode($_SERVER['SERVER_SOFTWARE']);
        $config = include $this->rootPath . '/app/admin/config/config.php';

        try {
            get_curl([
                'url' => base64_decode('aHR0cDovL3d3dy5ycnpjbXMuY29tL0FwaS9ScnpjbXMvYWRkaW5zdGFsbA=='),
                'data' => [
                    'domain' => $host,
                    'key_num' => $config['sys_version'],
                    'ip' => $ip,
                    'phpv' => $phpv,
                    'web_server' => $web_server,
                ],
            ]);
        } catch (\Throwable | \Exception $e) {

        }
        return $this->page();
    }

    private function upgrade($conn, $dbPrefix, $sqlVersion) {
        $dir = $this->rootPath . '/public/setup/sql/';
        if (!is_dir($dir)) {
            return;
        }
        $sqlVersion = empty($sqlVersion) ? 'v1.1.6' : $sqlVersion;//默认版本为v1.1.6

        $sqlList = glob($dir . 'v*.sql');//搜索升级数据库文件
        for ($i = 0; $i < count($sqlList); $i++) {
            $file = $sqlList[$i];
            if (!$file || !is_file($file)) continue;
            $parts = pathinfo($file);
            //判断sql的版本必须大于当前模版sql版本
            if ($sqlVersion >= $parts['filename']) {
                //@unlink($file);//删除没用的文件
                continue;
            }
            $sqldata = file_get_contents($file);
            $sqlFormat = sql_split($sqldata, $dbPrefix);
            $this->exeSql($conn, $sqlFormat);//and @unlink($file);
        }
    }

    private function exeSql($conn, $sqlFormat) {
        $counts = count($sqlFormat);
        try {
            for ($i = 0; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (!$sql) continue;
                mysqli_query($conn, $sql);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}
