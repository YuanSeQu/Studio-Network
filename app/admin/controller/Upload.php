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

class Upload extends BaseController
{

    var $upConfig = [];

    protected $appName = 'admin';

    protected $account;

    /**
     * 只有登录状态可以上传文件
     */
    protected function initialize() {
        parent::initialize();
        $this->account = $this->getAccount();
        $this->appName = $this->app->http->getName();
        if (!$this->account) {
            if ($this->request->isAjax()) {
                $this->request->isJson() or exit('no_login');
                exit(json_encode(['code' => 'no_login',]));
            } else {
                $url = $this->appName == 'admin' ? U('Login/index') : U('/');
                $this->redirect($url);
            }
            exit;
        }
        $this->setConfig();
    }

    /**
     * 初始化上传配置
     */
    protected function setConfig() {
        set_upload_config();
        $upConfig = C('imgspace.config');
        $this->upConfig = $upConfig;
    }

    public function index() {
        $type = I('get.type');

        if (!$type || !isset($this->upConfig[$type])) {
            $this->error('参数错误！');
        }
        $this->upload($type);
    }

    /**
     * 获取文件保存路径
     * @param $type
     * @return string
     */
    protected function getSavePath($type='') {
        return $type;
    }

    /**
     * 上传文件
     * @param $type
     * @throws \Exception
     */
    protected function upload($type) {
        if (!$this->request->isPost()) {
            return $this->error('非法上传');
        }

        if (!function_exists('finfo_open')) {
            $this->error('请先安装 fileinfo 扩展！');
        }

        if ($type == 'images') {
            ini_set('gd.jpeg_ignore_warning', 1);
        }

        //$finfo = finfo_open(FILEINFO_MIME_TYPE);
        //$tt=finfo_file($finfo,'E:\ggs.flv');

        $conf = $this->upConfig[$type];
        $files = $this->request->file();

        if (empty($files)) {
            ob_clean();
            if (!@ini_get('file_uploads')) {
                $this->error('请检查空间是否开启文件上传功能！');
            } else {
                $upload_max_filesize = ini_get('upload_max_filesize');
                $this->error('ERROR，请检查文件是否超过php配置的最大限制' . $upload_max_filesize);
            }
        }
        if (!$conf['fileMime']) {
            unset($conf['fileMime']);
        }
        $this->validate($files, ['file' => $conf], ['file.fileSize' => '上传文件过大', 'file.fileExt' => '上传文件后缀名必须为' . $conf['fileExt'],]);

        $filesystem = \think\facade\Filesystem::class;
        $config = $filesystem::getDiskConfig($filesystem::getDefaultDriver());

        $dir = $config['root'] . DIRECTORY_SEPARATOR . $type;
        is_dir($dir) or mkdir($dir, 0644, true);

        $isWatermark = I('post.watermark', 1);

        $savename = [];
        foreach ($files as $file) {
            $ext = strtolower($file->extension());
            if ($ext == 'php') {
                return $this->error('禁止上传PHP文件！');
            }
            /*验证图片一句话木马*/
            if ($type == 'images') {
                $imgstr = @file_get_contents($file->getPathname());
                if (false !== $imgstr && !preventShell($imgstr)) {
                    return $this->error('禁止上传木马图片！');
                }
            }

            $saveDir = $this->getSavePath($type);
            $path = $filesystem::putFile($saveDir, $file);

            if ($type == 'images' && $isWatermark !== 'false') {
                img_watermark($config['root'] . '/' . $path);//图片水印处理
            }

            $url = $config['url'] . '/' . $path;
            $savename[] = str_replace('\\', '/', $url);
        }

        service('FileUploadEnd', $savename, $msg);
        $msg and $this->error($msg);

        $this->success('', $savename[0], $savename);
    }
}