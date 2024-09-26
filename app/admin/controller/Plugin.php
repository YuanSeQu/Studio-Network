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
use app\plugin\lib\Common;

class Plugin extends Base
{
    function index()
    {
        $root = root_path();
        $dirs = array_map('basename', glob($root . 'public/addons/*', GLOB_ONLYDIR));
        $errormsg = "";
        if ($dirs === false) {
            $errormsg = '插件目录不可读';
        }
        if ($dirs !== false) {
            $pcodes = M('plugin')->column('code');
            foreach ($dirs as $pluginDir) {
                $pluginDir =parse_name($pluginDir, 1);
                $pindex = array_search($pluginDir, $pcodes);
                if ($pindex === false) {
                    $class = Common::plugin_get_class($pluginDir);
                    if (!class_exists($class)) {
                        continue;
                    }
                    $obj = new $class;
                    $pinfo = $obj->info;
                    $pinfo['config']=$pinfo['config']?json_encode($pinfo['config']):"";
                    $pinfo['addtime'] = time();
                    M('plugin')->strict(false)->insert($pinfo);
                } else {
                    unset($pcodes[$pindex]);
                }
            }
            if ($pcodes) {
                M('plugin')->where('code', 'in', $pcodes)->delete();
            }
        }

        $this->pagedata['tabs'] = [
            ['name' => '我的插件', 'class' => 'current',],
            ['name' => '插件市场', 'url' => U('Plugin/store'),],
        ];
        $vthis=$this;
        $vthis->isPluginFirst=true;
        $this->pagedata['columns'] = [
            ['field' => 'cz', 'title' => '操作', 'width' => '200', 'callback' => function ($item) {
                $html = '';
                if($item['status']==0)
                {
                    $html .= '<a href="' . U('Plugin/install', ['id' => $item['id'],]) . '" msg="确定要安装吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs plugin-installbtn">安装</a>';
                }
                else {
                    if($item['ishome'])
                        $html .= '<a target="dialog" options="{title:\'插件配置\',area:[\'680px\',\'500px\']}" href="' .  U('Plugin/basic', ['id' => $item['id'],]) . '" class="layui-btn layui-btn-xs">配置</a>';
                    else
                        $html .= '<a class="layui-btn layui-btn-xs layui-btn-disabled" title="该插件没有前台无需配置">配置</a>';
                    if($item['isadmin'])
                        $html .= '<a target="'.($item['isadmin']==1?'page':'_blank').'" href="' . Common::plugin_get_url($item['code'].'://Admin/index') . '" class="layui-btn layui-btn-xs">管理</a>';
                    else
                        $html .= '<a class="layui-btn layui-btn-xs layui-btn-disabled">管理</a>';
                    if ($item['status'] == 1) {
                        $html .= '<a href="' . U('Plugin/toggle', ['id' => $item['id'],]) . '" msg="确定要禁用吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">禁用</a>';
                    } else {
                        $html .= '<a href="' . U('Plugin/toggle', ['id' => $item['id'],]) . '" msg="确定要启用吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">启用</a>';
                    }
                    $html .= '<a href="' . U('Plugin/uninstall', ['id' => $item['id'],]) . '" msg="确定要卸载吗？" target="confirm" class="layui-btn layui-btn-danger layui-btn-xs">卸载</a>';
                }
                return $html;
            }],
            ['field' => 'code', 'title' => '编码', 'width' => '120'],
            ['field' => 'name', 'title' => '名称', 'width' => '200','align'=>'left', 'callback' => function ($item) {
                $html = '<a class="cl-38f" href="http://www.rrzcms.com/Plugins/plugininfo/code/' . $item['code'] . '.html" target="_blank">' . $item['name'] . '</a>';
                return $html;
            }],
            ['field' => 'desc', 'title' => '描述', 'width' => '300','align'=>'left'],
            ['field' => 'version', 'title' => '版本', 'width' => '100'],
            ['field' => 'onlineup', 'title' => '更新', 'width' => '100','callback'=>function($item)use($vthis){
                if($vthis->isPluginFirst) {
                    $pdatas = $vthis->pagedata['data'];
                    $vthis->isPluginFirst = false;
                    $vthis->updatePlugins = Common::plugin_check_version(array_column($pdatas, 'code'), array_column($pdatas, 'version'));
                }
                if($vthis->updatePlugins&&in_array($item['code'],$vthis->updatePlugins)) {
                    $html = '<a target="dialog" title="插件升级" class="layui-btn layui-btn-xs" options="{title:\'新版本升级\',area:[\'400px\']}" href="' . U('Plugin/update', ['code' => $item['code']]) . '">升级</a>';
                    $html .= '<a target="_blank" title="升级日志" class="layui-btn layui-btn-xs" href="http://www.rrzcms.com/Plugins/plugininfo/code/' . $item['code'] . '.html?pluginlx=uplog" >日志</a>';
                    return $html;
                }
                return '已最新';
            }],
            ['field' => 'status', 'title' => '状态', 'width' => '100', 'type' => 'enum', 'enum' => ['0' => '未安装', '1' => '启用', '2' => '禁用'],],
            ['field' => 'isload', 'title' => '加载方式', 'width' => '100', 'type' => 'enum', 'enum' => ['0' => '手动加载', '1' => '自动加载'],],
            ['field' => 'sort', 'title' => '加载顺序', 'width' => '100'],
        ];
        $this->pagedata['model'] = M('plugin')->field('id,code,name,desc,version,status,isload,ishome,isadmin,sort')->order('id desc');
        $this->pagedata['fixedColumn'] = true;
        $this->pagedata['grid_class'] = 'js-grid-goods';
        $this->assign('errormsg', $errormsg);
        return $this->grid_fetch();
    }
    //安装插件
    function install()
    {
        $id = I('get.id');
        $pcode = M('plugin')->where('id', $id)->value('code');
        if (!$pcode)
            $this->error("插件不存在！");
        $class = Common::plugin_get_class($pcode);
        if (!class_exists($class)) {
            $this->error("插件不存在！");
        }
        $plugin = new $class;
        $result = $plugin->install($emsg);
        if ($result) {
            clearCache(true);
            $this->success('安装成功！', true);
        } else
            $this->error($emsg, ['pcode' => $pcode]);
    }
    //卸载插件
    function uninstall()
    {
        $id = I('get.id');
        $pcode = M('plugin')->where('id', $id)->value('code');
        if (!$pcode)
            $this->error("插件不存在！");

        $class = Common::plugin_get_class($pcode);
        $emsg="";
        if (class_exists($class)) {
            $plugin = new $class;
            $result=$plugin->uninstall($emsg);
        }
        else
        {
            $result = M('plugin')->where('id', $id)->delete();
        }
        if($result) {
            clearCache(true);
            $this->success('卸载成功!', true);
        }
        else
            $this->error('卸载失败，'.$emsg.'!');
    }
    //启用禁用插件
    function toggle()
    {
        $id = I('get.id');
        $pinfo = M('plugin')->field('code,status')->where('id', $id)->find();
        if (!$pinfo) {
            $this->error('插件不存在！');
        }

        $class = Common::plugin_get_class($pinfo['code']);
        if (!class_exists($class)) {
            $this->error("插件不存在！");
        }
        $plugin = new $class;
        if ($pinfo['status'] == 1) {
            $result = $plugin->disable($emsg);
            if ($result) {
                clearCache(true);
                $this->success('禁用成功！', true);
            }
            else
                $this->error($emsg);
        } else {
            $result = $plugin->enable($emsg);
            if ($result) {
                clearCache(true);
                $this->success('启用成功！', true);
            }
            else
                $this->error($emsg);
        }
    }
    //插件更新
    function update()
    {
        $this->assign('code', I('get.code'));
        return $this->fetch();
    }
    //基础配置
    function basic()
    {
        $id = I('get.id');
        if (!$this->request->isPost()) {
            $path = root_path('public' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'pc' . DIRECTORY_SEPARATOR);
            $dirInfo = scandir($path);
            $temps = [];
            $row = M('plugin')->field('id,code,isload,loadtemp,sort')->where('id', $id)->find();
            $loads=[];
            if($row['loadtemp'])
                $loads = explode(',', $row['loadtemp']);
            foreach ($dirInfo as $name) {
                if (in_array($name, ['.', '..']) || is_dir($path . $name)) continue;
                $dv = ':' . (str_replace(strrchr($name, "."), "", $name));
                $temps[] = ['name' => $name, 'value' => $dv, 'ischeck' => in_array($dv, $loads) ? 1 : 0];
            }
            $this->assign('temps', $temps);
            $this->assign('row', $row);
            return $this->fetch();
        }
        $data = I('post.');
        if (is_numeric($id)) {
            if(isset($data['loadtemp'])&&$data['loadtemp'])
                $data['loadtemp']=implode(',',$data['loadtemp']).',';
            else
                $data['loadtemp']='';
            $rs = M('plugin')->where('id', $id)->save($data);
            $rs === false and $this->error('保存失败！');
            clearCache(true);
            $this->success('保存成功！', true);
        }
        $this->error('保存失败！');
    }
    //插件市场
    function store()
    {
        $pindex = I('post.pindex', 1);
        $errormsg = "";
        $pview = "";
        $total = 0;
        $rdata = Common::plugin_get_list($pindex);
        if ($rdata) {
            $total = $rdata['total'];
            $rdata['uses'] = M('plugin')->where('status!=0')->column('code');
            $rdata['version']=C('config.sys_version');
            $this->assign($rdata);
            $pview = $this->fetch('list');
        } else {
            $errormsg = "获取云插件数据失败！";
        }
        if (!$this->request->isPost()) {
            $this->assign(['pview' => $pview, 'total' => $total, 'errormsg' => $errormsg]);
            return $this->fetch();
        } else {
            return $pview;
        }
    }
    //插件市场安装\升级插件
    function store_install()
    {
        $pcode = I('post.code');
        $isup = I('post.isup', 0);
        $rdata = Common::plugin_online_install($pcode, $isup);
        if ($rdata['status'] == true)
            $this->success('安装成功！');
        else {
            $this->error($rdata['msg'], '', ['msgtype' => $rdata['msgtype']]);
        }
    }
}