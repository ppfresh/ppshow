<?php 

namespace app\back\controller;
use think\Controller;

class StaticPage extends Controller{

    public function index()
    {
        # 渲染模板
        $content = $this->fetch('front@shop/index'); //渲染模板,模块@控制器/视图名
        # 写入文件
        $file = ROOT_PATH . 'public/html/index.html';
        $result = file_put_contents($file, $content);
        if ($result > 0) {
            $this->success('首页模板生成成功', 'back/manage/dashboard');
        } else {
            $this->success('首页模板生成失败', 'back/manage/dashboard');
        }
    }
}