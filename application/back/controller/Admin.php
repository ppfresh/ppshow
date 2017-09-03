<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 19:55
 */

namespace app\back\controller;
use app\back\Model\RoleAction as RoleActionModel;
use app\back\Model\RoleAdmin as RoleAdminModel;
use app\back\validate\AdminCreate;
use app\back\validate\AdminSet;
use app\back\model\Role as RoleModel;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;
use app\back\model\Admin as AdminModel;
use think\Session;
use think\Validate;
use think\View;

class Admin extends Controller
{
   
    /**
     * 设置方法(包括添加,修改两个功能)
     */
    public function set($id=null)
    {
        #   实例化请求对象
        $request = Request::instance();
        #   get提交
        // get 请求, 展示表单form
        if ($request->isGet()) {
            // 管理员数据, 可能没有
            $row = AdminModel::get($id);

            # 获取所有角色
            $roles = Db::name('Role')->select();
//            dump($roles);die;
            # 获取对应的role分组信息
            $checkeds = RoleAdminModel::where('admin_id',$id)->column('role_id');

            // 可能存在错误消息和错误数据
            return $this->fetch('set', [
                // id也分配过去
                'id' => $id,
                // 分配的数据, 从session中获取, 并设置默认值
                'errorInfo' => Session::get('errorInfo') ?: [],
                // 数据, 可能是错误数据或编辑数据, 错误数据优先
                'data' => Session::get('errorData') ?: $row,
                'roles' => $roles,
                'checkeds'=>$checkeds
            ]);
        }
        #   post提交
        elseif ($request->isPost()) {
            //1.接受数据
//            $data = $request->post();   //请求对象的方式
                $data = input('post.');


            //2.校验数据
            $volidate = new AdminSet();
            if(!$volidate->batch(true)->check($data)){
                //校验未通过
                return $this->redirect('set',['id'=>$id],304,[
                    'errorInfo' =>  $volidate->getError(),
                    'errorData' =>  $data
                ]);
            }

            //3.添加或修改数据
            if (empty($id)) {
                // 新模型对象
                //对密码进行加盐加密
                $model = new AdminModel();
                $salt = mt_rand(1,999999);
                $data['salt'] = $salt;
                $data['password'] = md5($data['password'].$salt);
            } else {
                $model = AdminModel::get($id);
            }


            //将roles保存,删除数据中的roles
            $roles = $data['roles'];
            unset($data['roles']);


            // 执行返回受影响的记录数
            $affectedRows = $model
                // 允许自动赋值的字段
                ->allowField([
                    
                ])
                ->data($data)
                ->save();


            if ($affectedRows !== false) {
                # 将角色分组信息保存到role_admin关联表
                ## 删除之前存在的关联关系
                RoleAdminModel::where('admin_id',$id)->delete();
                ##更新新的关联关系
                $rows = array_map(function($role_id) use($id){
                    return [
                        'admin_id'=>$id,
                        'role_id'=>$role_id
                    ];
                },$roles);

                (new RoleAdminModel())->saveAll($rows);

                # 4. 重定向到index动作
                return $this->redirect('index');
            } else {
                // 插入错误
                return $this->redirect('set');
            }
        }
    }


    /**
     * 管理员展示
     */
    public function index()
    {
        #   1.搜索
        $model = new AdminModel();
        $pageQuery = [];
        //暂时没有条件,根据不同的功能添加
        $this->assign('pageQuery',$pageQuery);


        #   2.排序
        $orderStr = '';
        $order_field = input('order_field',null);
        $order_type = input('order_type','asc');
        if(!is_null($order_field)){
            //存在排序字段
            $orderStr = $order_field.' '.$order_type;
        }
        $model->order($orderStr);
        //将排序的字段和方式分配到模板
        $this->assign('order_field', $order_field);
        $this->assign('order_type',$order_type);


        #   3.分页
        //查询数据
        $limit = null;
        //给分页链接添加关键字参数传递
        $rows = $model->paginate($limit,false,[
            'query' =>  $pageQuery
        ]);
        return $this->fetch('index',[
            'rows'  =>  $rows
        ]);
    }

    /**
     *登录方法
     */
    public function login()
    {
        $request = request();

        # 如果是get请求
        if($request->isGet()){
            # 展示登录界面
            return $this->fetch('login',[
                'errorInfo' =>  Session::get('errorInfo')? : ''
            ]);
        }
        # 如果是post请求
        elseif($request->isPost()){

            $user = input('user');
            $password = input('password');
            //验证用户名
            #因为模型的查询结果是一个模型的对象,不可添加自定义的变量在里面
//            $admin = (new AdminModel())->where('user',$user)->find();
            $admin = Db::name('admin')->where('user',$user)->find();


            if($admin){
                //说明有该用户,判断密码是否正确
//                dump($admin['password']);
//                dump(md5($password.$admin['salt']));die;
                if($admin['password'] == md5($password.$admin['salt'])){

                    //说明用户名密码都正确
                    # 保存用户的登录信息(保存登录时间,用户超时验证)
                    $admin['time'] = time();
                    Session::set('admin',$admin);

                    # 跳转到后台首页
//                    $this->redirect('brand/index');
                    # 跳转到登录前请求页面(pull()表示取出并删除)
                    redirect(Session::has('returnUrl')? Session::pull('returnUrl'):'manage/dashboard')->send();
                    die;
                }
            }

            return $this->redirect('login',[],'302',[
                $errorInfo = '用户名或密码错误'
            ]);
        }

    }

    /*
     * 注销登录的方法
     */
    public function logout()
    {
        $request = request();
        if($request->isPost()){

            //清除session数据
            Session::delete('admin');

            //跳转到登录页面
            return $this->redirect('login');
        }
    }

    /**
     * 批量删除
     */
    public function batch()
    {
        #   接收数据
        //注意:用input接收数组数据是,必须加/a;
        //并且这里不建议使用默认值,空数组会导致全部删除,或修改destroy
        $ids = input('selected/a',[]);
        //dump($ids);die;
        AdminModel::destroy($ids);

        return $this->redirect('index');
    }

}