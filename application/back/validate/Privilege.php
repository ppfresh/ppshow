<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/9/3
 * Time: 0:53
 */

namespace app\back\validate;


use think\Db;
use think\Session;

class Privilege
{
    /**
     * 检查用户是否有访问权限
     * @param null $route
     * @param null $user_id
     */
    public static function check($route=null,$user_id=null)
    {
        $request = request();
        #获得用户的动作权限
        if(is_null($user_id)){
            //自动获取id
            $user_id = Session::get('admin.id');
        }

        $routes = Db::name('Role_admin')
            ->alias('ra')
            ->join('__ROLE_ACTION__ rac','ra.role_id=rac.role_id','LEFT')
            ->join('__ACTION__ a','rac.action_id=a.id','LEFT')
            ->where('ra.admin_id',$user_id)
            ->column('route');


        # 获取用户的访问路由
        if(is_null($route)){
            $route = $request->module().'/'.strtolower($request->controller()).'/'.$request->action();
        }


        #判断用户是否可以访问
        if(in_array($route,$routes)){
            return true;
        }

        return false;

    }

}