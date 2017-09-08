<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 19:55
 */

namespace app\back\controller;
use app\back\validate\AttributeCreate;
use app\back\validate\AttributeSet;
use think\Config;
use think\Controller;
use think\Request;
use app\back\model\Attribute as AttributeModel;
use think\Session;
use think\Validate;
use app\back\model\AttributeGroup as AttributeGroupModel;

class Attribute extends Controller
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
            // 属性数据, 可能没有
            $row = AttributeModel::get($id);

            // 可能存在错误消息和错误数据
            return $this->fetch('set', [
                // id也分配过去
                'id' => $id,
                // 分配的数据, 从session中获取, 并设置默认值
                'errorInfo' => Session::get('errorInfo') ?: [],
                // 数据, 可能是错误数据或编辑数据, 错误数据优先
                'data' => Session::get('errorData') ?: $row,
                //分配属性组
                'attributeGroupList'=> (new AttributeGroupModel())->select()
            ]);
        }
        #   post提交
        elseif ($request->isPost()) {
            //1.接受数据
//            $data = $request->post();   //请求对象的方式
                $data = input('post.');

            //2.校验数据
            $volidate = new AttributeSet();
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
                $model = new AttributeModel();
            } else {
                $model = AttributeModel::get($id);
            }

            // 执行返回受影响的记录数
            $affectedRows = $model
                // 允许自动赋值的字段
                ->allowField([
                    
                ])
                ->data($data)
                ->save();

            if ($affectedRows !== false) {
                # 4. 重定向到index动作
                return $this->redirect('index');
            } else {
                // 插入错误
                return $this->redirect('set');
            }
        }
    }


    /**
     * 属性展示
     */
    public function index()
    {
        #   1.搜索
        $model = new AttributeModel();
        $pageQuery = [];

        //显示所有属性组信息
        $filterAttributeGroupList = (new AttributeGroupModel())->select();
        $this->assign('filterAttributeGroupList',$filterAttributeGroupList);

        //根据属性组显示
        $filter_attribute_group_id = input('filter_attribute_group_id',null);
        //存在搜索条件
        if(!empty($filter_attribute_group_id)){
            //组织查询条件
            $model->where('attribute_group_id',$filter_attribute_group_id);
            $pageQuery = ['filter_attribute_group_id'=>$filter_attribute_group_id];
        }

        //反显查询条件
        $this->assign('filter_attribute_group_id',$filter_attribute_group_id);


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
            'rows'  =>  $rows,
        ]);
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
        AttributeModel::destroy($ids);

        return $this->redirect('index');
    }

}