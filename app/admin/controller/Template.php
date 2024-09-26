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


class Template extends Base
{

    var $tempType = [
        '/block/' => '存放 footer.html、header.html 等公用文件',
        '/index.html/' => '首页',
        '/search.html/' => '搜索页',
        '/brand.html/' => '品牌页',
        '/top.html/' => '页面上部内容',
        '/left.html/' => '页面左侧内容',
        '/right.html/' => '页面右侧内容',
        '/bottom.html/' => '页面下部内容',
        '/head(er)?.html/' => '页面头部内容',
        '/foot(er)?.html/' => '页面底部内容',
        '/(kefu|eonline).html/' => '在线客服',

        '/^node_single(.*)?.html/' => '栏目单页',
        '/^node((?!_single).*)?.html/' => '文章列表页',
        '/^article(.*)?.html/' => '文章详情页',
        '/^cat(.*)?.html/' => '产品列表页',
        '/^item(.*)?.html/' => '产品详情页',

        '/cat_all.html/' => '全部产品',
        '/(?<!node)_single.html/' => '单页型',
        '/_about.html/' => '关于我们',
        '/_case.html/' => '案例展示',
        '/_(img|image).html/' => '图片型',
        '/_liuyan.html/' => '在线留言',
        '/_contact.html/' => '联系我们',
    ];

    private $tmplFileList = [];

    /**
     * 文件列表
     */
    function fileList() {

        $dir = rtrim(I('path', 'template'), '/');
        if (strpos($dir, '.') !== false || strpos($dir, 'template') === false) {
            exit('error|目录错误！');
        }
        $root = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR;
        $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;
        is_dir($path) or exit('error|目录不存在！');

        $this->pagedata['tabs'] = [
            ['name' => '文件列表'],
        ];
        $this->pagedata['actions'] = [
            ['label' => '新建文件', 'href' => U('Template/addFile') . '?path=' . urlencode($dir), 'target' => 'page',],
        ];
        $that = $this;

        $this->pagedata['columns'] = [
            ['field' => 'name', 'title' => '文件名', 'width' => '500', 'align' => 'left', 'callback' => function ($item) use ($dir, $that) {
                $html = '';
                $icon = str_replace('fileicon-', '', $item['icon']);
                if ($item['name'] === '上级目录') {
                    $html = '<a href="' . U('Template/fileList') . '?path=' . urlencode($item['path']) . '" target="page">上级目录</a>（当前目录：' . $dir . '）';
                } elseif ($item['type'] == 'dir') {
                    $html = '<a href="' . U('Template/fileList') . '?path=' . urlencode($item['path']) . '" target="page">' . $item['name'] . '</a>';
                } elseif (in_array($icon, ['code', 'web', 'text'])) {
                    $html = '<a href="' . U('Template/addFile') . '?path=' . urlencode($item['path']) . '" target="page">' . $item['name'] . '</a>';
                } elseif ($icon == 'pic') {
                    $html = '<a class="tips-img" src="/' . $item['path'] . '" href="/' . $item['path'] . '" target="_blank">' . $item['name'] . '</a>';
                } else {
                    $html = '<span>' . $item['name'] . '</span>';
                }
                $temp = $that->get_file_name($item);
                $temp and $temp = '<span class="cl-888">（' . $temp . '）</span>';
                return '<div class="file-name fileicon ' . $item['icon'] . '">' . $html . $temp . '</div>';
            }, 'class' => 'pl10',],
            ['field' => 'size', 'title' => '文件大小', 'width' => '120',],
            ['field' => 'time', 'title' => '更新时间', 'type' => 'time', 'width' => '150',],
            ['field' => 'cz', 'title' => '操作', 'width' => '180', 'callback' => function ($item) use ($dir) {
                $html = '';
                if ($item['type'] == 'file' && in_array($item['ext'], ['js', 'html', 'css', 'txt'])) {
                    $html = '<a href="' . U('Template/addFile') . '?path=' . urlencode($item['path']) . '" target="page" class="layui-btn layui-btn-xs">编辑</a>';
                }
                if ($item['type'] == 'file' && $item['ext'] == 'html') {
                    $html .= '<a href="' . U('Template/extractHtml') . '?path=' . urlencode($item['path']) . '" target="page" class="layui-btn layui-btn-danger layui-btn-xs">可视化修改</a>';
                }
                return $html;
            }],
        ];

        $list = $dir == 'template' ? [] : [
            [
                'type' => 'dir',
                'name' => '上级目录',
                'size' => '',
                'time' => '',
                'icon' => 'fileicon-dir-top',
                'ext' => 'dir',
                'path' => substr($dir, 0, strrpos($dir, '/')),
            ],
        ];
        $dirs = [];
        $files = [];
        $dirInfo = scandir($path);
        foreach ($dirInfo as $name) {
            if (in_array($name, ['.', '..'])) continue;
            $name1 = @iconv('GBK', 'UTF-8//TRANSLIT', $name);

            $file = $path . $name1 ?: $name;
            $fileType = is_dir($file) ? 'dir' : '';
            $icon = fileiconByType($file, $fileType);

            $item = [
                'type' => is_dir($file) ? 'dir' : 'file',
                'name' => $name,
                'size' => is_dir($file) ? '' : size_local_file($file),
                'time' => is_dir($file) ? '' : filemtime($file),
                'icon' => $icon,
                'ext' => $fileType,
                'path' => $dir . '/' . $name,
            ];
            is_dir($file) ? $dirs[] = $item : $files[] = $item;
        }
        $list = array_merge($list, $dirs, $files);
        unset($dirs, $files);

        $this->pagedata['data'] = $list;

        $this->pagedata['pk_field'] = 'ext';
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['isPage'] = false;
        $this->pagedata['grid_class'] = 'fileList-view js-view-temp-fileList';

        return $this->grid_fetch();
    }

    private function get_file_name($item) {
        if (!$this->tmplFileList) {
            $lib = new \app\admin\lib\Template('/pc');
            $this->tmplFileList = $lib->getTmplList();
        }
        $name = $item['name'];
        if (isset($this->tmplFileList[$name])) {
            return $this->tmplFileList[$name];
        }
        $temp = [];
        foreach ($this->tempType as $k => $val) {
            if (preg_match($k, $name)) {
                $temp[] = $val;
            }
        }
        $temp = implode(' - ',$temp);
        return $temp;
    }

    function extractHtml() {
        $root = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR;
        if (!$this->request->isPost()) {
            $dir = rtrim(I('get.path'), '/');
            if (strpos($dir, 'template') === false) {
                exit('error|目录错误！');
            }
            $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            is_file($path) or exit('error|该文件不支持提取！');

            $content = file_get_contents($path);
            $lib = new \app\admin\lib\Template($dir);
            $list = $lib->extract($content);

            $this->assign('list', $list);

            $this->assign('path', $dir);
            $this->assign('dir', dirname($dir));
            return $this->fetch();
        }
        $dir = rtrim(I('post.path'), '/');
        if (strpos($dir, 'template') === false) $this->error('目录错误');
        $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        is_file($path) or $this->error('操作文件不存在！');

        $content = file_get_contents($path);
        $lib = new \app\admin\lib\Template($dir);
        $content = $lib->replace($content, I('post.'));
        file_put_contents($path, $content);

        $this->success('操作成功！', U('Template/fileList') . '?path=' . urlencode(dirname($dir)));
    }

    /**
     * 添加、编辑、保存文件
     */
    function addFile() {
        $root = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR;
        if (!$this->request->isPost()) {
            $dir = rtrim(I('get.path'), '/');
            if (strpos($dir, 'template') === false) {
                exit('error|目录错误！');
            }
            $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            $info = [
                'name' => 'newfile.html',
                'content' => '',
                'mode' => 'htmlmixed',
                'isNew' => true,
                'path' => $dir,
            ];
            if (is_file($path)) {
                $parts = pathinfo($path);
                $dir = str_replace('/' . $parts['basename'], '', $dir);
                $info['name'] = $parts['basename'];
                $info['content'] = file_get_contents($path);
                $info['isNew'] = false;
                if ($parts['extension'] == 'js') {
                    $info['mode'] = 'text/javascript';
                } elseif ($parts['extension'] == 'css') {
                    $info['mode'] = 'text/css';
                } elseif ($parts['extension'] == 'json') {
                    $info['mode'] = 'application/json';
                }
            } elseif (is_dir($path)) {
                strpos($path, '.') !== false and exit('error|目录错误！');
            } else {
                exit('error|目录错误！');
            }
            $this->assign('dir', $dir);
            $this->assign('info', $info);
            return $this->fetch();
        }
        $dir = rtrim(I('post.path'), '/');
        $name = I('post.name');
        $content = I('post.content');

        if (strpos($dir, 'template') === false) $this->error('目录错误');

        //安全验证
        if (preg_match('#<([^?]*)\?php#i', $content) || preg_match('#<\?(\s*)=#i', $content) ||
            (preg_match('#<\?#i', $content) && preg_match('#\?>#i', $content)) || preg_match('#\{php([^\}]*)\}#i', $content)) {
            $this->error("模板里不允许有php语法，为了安全考虑，请通过FTP工具进行编辑上传。");
        }

        $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        if (is_file($path)) {
            $parts = pathinfo($path);
            $dir = str_replace('/' . $parts['basename'], '', $dir);
            $new = str_replace($parts['basename'], $name, $path);
            $parts = pathinfo($new);
            if (!in_array($parts['extension'], ['js', 'html', 'css', 'txt', 'json'])) {
                $this->error('只允许操作文件类型如下：html|js|css|txt|json');
            }
            file_put_contents($path, $content);
            rename($path, $new) or $this->error('操作失败，请检查文件目录权限！');
        } elseif (is_dir($path)) {
            $path = $path . DIRECTORY_SEPARATOR . $name;
            $parts = pathinfo($path);
            if (!in_array($parts['extension'], ['js', 'html', 'css', 'txt', 'json'])) {
                $this->error('只允许操作文件类型如下：html|js|css|txt|json');
            }
            $rs = file_put_contents($path, $content);
            $rs === false and $this->error('操作失败，请检查文件目录权限！');
        } else {
            $this->error('目录错误！');
        }
        $this->success('操作成功！', U('Template/fileList') . '?path=' . urlencode($dir));
    }

}
