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

use think\Exception;

class Index extends Base
{

    protected function initialize() {
        parent::initialize();
        $action = $this->request->action();
        if (in_array($action, ['storage', 'imgspace'])) {
            set_upload_config();
        }
    }

    public function index() {

        $this->assign('account', $this->account);

        $menus = C('menus');
        $this->assign('menus', $menus);

        $statistic = [
            'articles_count' => M('articles')->where('deltime',  0)->count(),
            'goods_count' => M('goods')->where('deltime', 0)->count(),
            'links_count' => M('site_links')->count(),
            'admin_count' => M('admin')->count(),
        ];

        $dashboard = $this->fetch('index/dashboard', [
            'statistic' => $statistic,
            'sys_info' => get_sys_info(),
            'global' => $this->getGlobal(),
        ]);
        $this->assign('dashboard', $dashboard);

        return $this->fetch();
    }

    function authortoken() {
        $url = base64_decode('aHR0cDovL3d3dy5ycnpjbXMuY29tL0FwaS9ScnpjbXMvY2hlY2tkb21haW4=');
        $vaules = [
            'domain' => urldecode($this->request->host(true)),
        ];
        $url .= '?' . http_build_query($vaules);
        $params = get_curl($url, 'json');
        if (is_array($params) && 'success' == $params['status']) {
            $authortoken_code = $params['data']['code'];
            sysConfig('website.authortoken_code', $authortoken_code);
            session('isset_author', false);
            clearCache(true);

            adminLog('验证商业授权');
            $this->success('域名授权成功', ['jump' => true]);
        }
        $this->error('域名（' . $this->request->domain() . '）未授权');
    }

    function storage() {
        $type = I('get.type');
        if (!in_array($type, ['video', 'audio', 'images', 'other'])) {
            $this->error('参数错误！');
        }
        $isAll = I('get.is_all', false);

        $filesystem = \think\facade\Filesystem::class;
        $path = $filesystem::path('') . $type . '/';

        if (!$this->request->isPost()) {

            is_dir($path) or mkdir($path, 0777, true);
            $list = getDirList($path);

            $this->assign('type', $type);
            $this->assign('dirList', $list);
            $this->assign('tabs', C('imgspace.tabs'));
            $this->assign('isAll', $isAll);

            if ($type == 'images') {
                $this->assign('multiple', $_GET['multiple'] ?? false);
                $isWatermark = sysConfig('watermark.is_enable', null, 0);
                if (I('get.watermark', 'true') === 'false') {
                    $isWatermark = 0;
                }
                $this->assign('isWatermark', $isWatermark);
            }

            return $this->fetch();
        }
        $config = $filesystem::getDiskConfig($filesystem::getDefaultDriver());

        $dir = I('post.name');
        if (strpos($dir, '.') !== false) return;

        $path .= $dir . '/';
        if (!is_dir($path)) return;
        $fileList = getFileList($path);
        $list = [];
        foreach ($fileList as $path) {
            $url = $config['url'] . '/' . $type . '/' . $dir . '/' . $path;
            $parts = pathinfo($url);
            $list[] = [
                'url' => $url,
                'title' => $parts['basename'],
                'ext' => $parts['extension'],
                'name' => $parts['filename'],
            ];
        }

        $this->assign('url', $config['url'] . '/' . $type . '/' . $dir);
        $this->assign('type', $type);
        $this->assign('list', $list);
        return $this->fetch('index/storage/list');
    }

    function check_upgrade_version() {
        $upgrade = new \app\admin\lib\Upgrade;
        $rs = $upgrade->checkVersion($msg); // 升级包消息
        $rs or $this->error($msg);
        $this->success($rs);
    }

    function OneKeyUpgrade() {
        $upgrade = new \app\admin\lib\Upgrade;
        $rs = $upgrade->OneKeyUpgrade($msg);
        $rs or $this->error($msg);
        $this->success($msg);
    }

    function setPopupUpgrade() {
        $popup_upgrade = I('popup_upgrade', 1);
        sysConfig('admin.popup_upgrade', $popup_upgrade);
        $this->success();
    }

    /**
     * 更多功能
     * @return string
     * @throws \Exception
     */
    function switch_map() {
        if (!$this->request->isPost()) {
            $users = sysConfig('users');
            $admin = sysConfig('admin');
            $webcache = sysConfig('webcache');
            $webcache or $webcache = ['switch' => 1, 'browser' => 1, 'html' => 1, 'data' => 1,];

            $this->assign('users', $users);
            $this->assign('admin', $admin);
            $this->assign('webcache', $webcache);
            return $this->fetch();
        }
        $name = I('post.name', null, 'trim');
        $value = I('post.value', null, 'trim');

        $data = [];
        if ($name == 'users.is_contribute' && $value) {
            $data = ['users.is_contribute' => 1, 'admin.web_user' => 1,];
            sysConfig('admin.web_user', 1);
        } elseif ($name == 'admin.web_user' && !$value) {
            $data = ['users.is_contribute' => 0, 'admin.web_user' => 0,];
            sysConfig('users.is_contribute', 0);
        } elseif ($name == 'admin.hide_plugin') {
            $value = $value ? 0 : 1;
        } elseif ($name == 'webcache.switch') {
            $name = 'webcache';
            $value = ['switch' => $value, 'browser' => $value, 'html' => $value, 'data' => $value,];
        }

        sysConfig($name, $value);
        $this->success('操作成功！', '', $data);
    }

    /**
     * 水印配置
     * @return string
     * @throws \Exception
     */
    function watermark() {
        if (!$this->request->isPost()) {
            $watermark = sysConfig('watermark', null, C('imgspace.watermark.default'));
            $this->assign('data', $watermark);
            return $this->fetch();
        }
        $data = I('post.', null, 'trim');
        if ($data['type'] == 'img') {
            $img = $data['img'];
            $img or $this->error('请上传水印图片！');
            //网络图片处理
            if (preg_match('/^(http(s)?:)?\/\//i', $img)) {
                $data['img'] = remoteimg_tolocal('', $img, false);
            }
        }
        sysConfig('watermark', $data);
        $this->success('操作成功！', 'Index/switch_map');
    }

    private function getGlobal() {
        $toStr = 'a' . 'r' . 'r' . '2' . 'S' . 't' . 'r';
        $security = C($toStr(['c2Vj', 'dXJp', 'dHk=']));
        ksort($security);
        $fun1 = $toStr(['V', 'Q', '==']);
        $fun2 = $toStr(['c3lz', 'Q29uZ', 'mln']);
        $fun3 = $toStr(['YXJyYX', 'lfc2xpY', '2U=']);

        $gk = $toStr($fun3($security, 0, 2));
        $gv = $toStr($fun3($security, 2, 2));
        $global[$gk] = $fun1($gv);

        $gk = $toStr($fun3($security, 4, 2));
        $gv = $toStr($fun3($security, 6, 3));
        $global[$gk] = $fun2($gv);

        $gk = $toStr($fun3($security, 9, 2));
        $gv = $toStr($fun3($security, 11, 2));
        $global[$gk] = $fun1($gv);

        $gk = $toStr($fun3($security, 13, 2));
        $gv = $toStr($fun3($security, 15, 3));
        $global[$gk] = $gv;

        $gk = $toStr($fun3($security, 18, 1));
        $gv = $toStr($fun3($security, 19, 2));
        $global[$gk] = $fun1($gv);

        return $global;
    }
}
