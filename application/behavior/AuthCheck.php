<?php 	

namespace app\behavior;
use app\back\validate\Privilege;
use think\Config;
use think\Db;
use think\Session;
use think\View;

class AuthCheck
{

	# 存在特殊的方法不需要校验login.html
	private $rules = [
		'admin'	=>['login']
	];


	public function run()
	{
		$request = request();

		# 防跳墙仅作用于后台
		$module = $request->module();
		if('back' != $module){
			return ;
		}

		# 排除例外不需要进行校验的动作
		//获取当前访问的动作
		$controller = strtolower($request->controller());
		$action = $request->action();

		if(!isset($this->rules[$controller]) || !in_array($action, $this->rules[$controller])){
			# 校验
			if(! Session::has('admin')){

			    # 实现直接跳转登录前的的请求路由
                $retrunUrl = $request->url(true);
                //记录下来请求路由
                Session::set('returnUrl',$retrunUrl);
			    //跳转登录页
				redirect('admin/login')->send();
				die;
            }

            # 判断用户是否有权限访问
            if(!Privilege::check()){
			    //没有访问权限
                redirect('admin/login')->send();
                die;
            }


            ## 获取当前用户访问的路由
            $route = $request->module().'/'.strtolower($request->controller()).'/'.$request->action();

            //已经登录成功
            # 验证用户信息是否超时
            $admin = Session::get('admin');
			$time = $admin['time'];

            if(time()-$time>Config::get('session_expire')){
                //说明超时,重新登录
                Session::delete('admin');
                redirect('admin/login')->send();
                die;
            }
            # 每次操作,重新刷新session
            Session::set('admin.time',time());

            # 分配用户信息到模板中
            View::share('admin',$admin);

		}
		return ;
	}
}












 ?>