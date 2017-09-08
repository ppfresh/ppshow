<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 19:55
 */

namespace app\back\controller;
use app\back\validate\ProductCreate;
use app\back\validate\ProductSet;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;
use app\back\model\Product as ProductModel;
use think\Session;
use think\Validate;
use \app\back\model\Sku as SkuModel;
use \app\back\model\StockStatus as StockStatusModel;
use \app\back\model\LengthUnit as LengthUnitModel;
use \app\back\model\WeightUnit as WeightUnitModel;
use \app\back\model\Brand as BrandModel;
use \app\back\model\Category as CategoryModel;
use \app\back\model\AttributeGroup as AttributeGroupModel;
use \app\back\model\ProductCategory as ProductCategoryModel;
use app\back\model\Attribute as AttributeModel;
use app\back\model\ProductAttribute;
use think\Image;
use app\back\model\Group;
class Product extends Controller
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
            //判断当前用户是否是该商品的创建管理员
            if(!is_null($id)){
                $product = ProductModel::get($id);
                if($product['admin_id'] != Session::get('admin.id')){
                    return $this->redirect('index',[],304,[
                        'errorInfo'=>'没有编辑权限'
                    ]);
                }
            }

            // 产品表数据, 可能没有
            $row = ProductModel::get($id);

            // 可能存在错误消息和错误数据
            return $this->fetch('set', [
                // id也分配过去
                'id'        =>  $id,
                // 分配的数据, 从session中获取, 并设置默认值
                'errorInfo' =>  Session::get('errorInfo') ?: [],
                // 数据, 可能是错误数据或编辑数据, 错误数据优先
                'data'      =>  Session::get('errorData') ?: $row,
                //分配库存单位
                'skuList'   =>  (new SkuModel())->select(),
                //分配库存状态
                'stockStatusList'   =>  (new StockStatusModel())->select(),
                //分配长度单位
                'lengthUnitList'    =>  (new LengthUnitModel())->select(),
                //分配重量单位
                'weightUnitList'    =>  (new WeightUnitModel())->select(),
                //分配品牌
                'brandList' => (new BrandModel())->select(),
                //分配分类
                'categoryList'      =>  (new CategoryModel())->getTree(),
                //分配已选的分配
                'checkedCategoryList'   =>  is_null($id)?[] : ProductCategoryModel::where('product_id',$id)->column('category_id'),
                //分配当前登录管理员的id
                'adminId'   => Session::get('admin.id'),
                //分配属性组
                'attributeGroupList'    =>  (new AttributeGroupModel())->select()
            ]);
        }
        #   post提交
        elseif ($request->isPost()) {
            //1.接受数据
//            $data = $request->post();   //请求对象的方式
                $data = input('post.');

            //2.校验数据
            $volidate = new ProductSet();
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
                $model = new ProductModel();
            } else {
                $model = ProductModel::get($id);
            }

            //插入数据前,清除主表中没有的字段数据
            unset($data['category_ids']);
            unset($data['attributes']);

            // 执行返回受影响的记录数
            $affectedRows = $model
                // 允许自动赋值的字段
                ->allowField(true)
                ->data($data)
                ->save();

            if ($affectedRows !== false) {

                # 更新拓展分类
                //先删除旧的关联数据
                ProductCategoryModel::where('product_id',$model->id)->delete();
                //添加新的关联关系(use是引入外部的变量)
                $pcRows = array_map(function($category_id) use($model){
                    return [
                        'product_id'=> $model->id,
                        'category_id'=>$category_id
                    ];
                },input('category_ids/a',[]));
                (new ProductCategoryModel())->allowField(true)->saveAll($pcRows);

                #更新产品与属性的关联
                #添加新的关联关系
                $paRrows = array_map(function($row) use($model){
                    $row['product_id'] = $model->id;
                    return $row;
                },input('attributes/a',[]));
                (new ProductAttribute())->allowField(true)->saveAll($paRrows);
                //saveAll()可以自动区分是更新还是添加

                # 4. 重定向到index动作
                return $this->redirect('index');
            } else {
                // 插入错误
                return $this->redirect('set');
            }
        }
    }


    /**
     * 产品表展示
     */
    public function index()
    {
        #   1.搜索:
        ## 默认查询未删除的 
        $type = input('type',null);
        if($type == 'circle'){
            $model = (new ProductModel)->where('is_delete','1');
        } else{
             $model = (new ProductModel())->where('is_delete','0');
        }
        $pageQuery = [];
        //暂时没有条件,根据不同的功能添加
        $this->assign('pageQuery',$pageQuery);


        #   2.排序
        $orderStr = '';
        $order_field = input('order_field','title');
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
        $limit = 10;
        //给分页链接添加关键字参数传递
        $rows = $model->paginate($limit,false,[
            'query' =>  $pageQuery
        ]);

        #获得每个商品对应的型号属性:
        //遍历要显示的在该页的每个商品
        $productIds = [];
        foreach($rows as $val){
            $productIds[] = $val['id'];
        }
       
        #查询型号信息
        $extends = Db::name('product_attribute')
                ->where('product_id','in',$productIds) 
                //这句语法就需要商品的id,没有这句语法,上面的查询也不需要
                //这种查询条件基于如果数量太多,可以这样优化
                ->where('is_extend',1)
                ->group('product_id')
                ->column('product_id,group_concat(value)');

        return $this->fetch('index',[
            'rows'  =>  $rows,
            'extends' => $extends,
            'type' => $type
        ]);
    }

  

    /**
     * 批量操作:放入回收站,彻底删除,组合,接触组合
     */
    public function batch()
    {
         #   接收数据
        //注意:用input接收数组数据是,必须加/a;
        //并且这里不建议使用默认值,空数组会导致全部删除,或修改destroy
        $ids = input('selected/a',[]);
        $operate = input('operate');
        
        //根据额外的参数,区分不同的操作
        switch($operate){
            ## 放入回收站
            case 'delete':
                ProductModel::where('id','in',$ids)->update(['is_delete'=>1]);
                //定向到回收站
                return $this->redirect('index');
                break;

            ## 彻底删除
            case 'relDelete':
                #删除关联关系
                ## 扩展分类,扩展属性,图像
                ProductAttribute::where('product_id','in',$ids)->delete();
                ProductCategoryModel::where('product_id','in',$ids)->delete();
                $imgRows = ProductModel::where('id','in',$ids)->column('image,image_thumb');
                foreach($imgRows as $value){
                    if(!empty($value['image'])){
                        @unlink(ROOT_PATH.'/pubclic/uploads/'.$value['image']);
                    }
                    //短路的写法
                    (!empty($value['image_thumb'])) && @unlink(ROOT_PATH.'/public/thumb/'.$value['image_thumb']);
                } 
                #删除商品本身
                ProductModel::destroy($ids);
                //定向到回收站
                return $this->redirect('index',['type'=>'circle']);
                break;

            ## 还原
            case 'undo' :
                ProductModel::where('id','in',$ids)->update(['is_delete'=>0]);
                //定向到列表
                return $this->redirect('index');
                break;

            ## 组合操作
            case 'group' :
                //组合时候有三种情况:
                //获得所选项的所有group_id
                $groupIds = (new ProductModel)
                ->where('group_id','<>',0)
                ->where('id','in',$ids)
                ->distinct(true)
                ->column('group_id');
                # 所选项都不属于任何一个组(生成一个新组)
                if(count($groupIds) == 0){
                    //生成一个新组
                    $group = (new Group());
                    $group->save();
                    $group_id = $group->id;  

                    //更新选中项的group_id
                    (new ProductModel)->where('id','in',$ids)->update(['group_id'=>$group_id]);
                }
                elseif(count($groupIds) == 1){
                    
                    ## 其中有一部分属于一个组,一部分没有所属组
                    $group_id = $groupIds[0];

                    //更新选中项的group_id
                    (new ProductModel)->where('id','in',$ids)->update(['group_id'=>$group_id]);
                }
                elseif(count($groupIds)>1){
                    
                    ## 有在不同所属组的(不进行处理)
                    return $this->redirect('index',[],302,['errorInfo'=>'处于多个分组的商品不能组合到一起']);
                }
                return $this->redirect('index');
                break;

        }
    }

    /**
     * 解除所属分组
     */
    public function unGroup($id){
        (new ProductModel)->where('id',$id)->update(['group_id'=>0]);
        return $this->redirect('index');
    }

    /**
     * 商品复制
     * @param  需要复制的商品 $id 
     */
    public function copy($id){
        # 复制数据
        $row = Db::name('product')->where('id',$id)->find();
       
        # 清除里面不需要的数据
        unset($row['id']);
        unset($row['create_time']);
        unset($row['update_time']);
        unset($row['upc']);
        unset($row['admin_id']);
        #生成复制商品
        $newProduct = new ProductModel();
        $newProduct->data($row)->save();
        

        #关联关系复制
        $newId = $newProduct->id;

        ## 拓展分类
        $categoryIds = Db::name('product_category')
        ->where('product_id',$id)
        ->column('category_id');
        //pc关联数据
        $pcData = array_map(function($categoryId) use($newId) 
        {
            return [
                'category_id'=>$categoryId,
                'product_id'=>$newId
            ];
        },$categoryIds);
        (new ProductCategoryModel())->saveAll($pcData);

        ## 扩展属性
        $attributeIds = Db::name('product_attribute')
        ->where('product_id',$id)
        ->column('attribute_id,value,sort,is_extend');
        
        //pa关联数据
        $paData = array_map(function($attribute)use($newId){
            $attribute['product_id'] = $newId;
            return  $attribute;
        },$attributeIds);
        (new ProductAttribute)->saveAll($paData);

        # 保存完成,跳转首页
        return $this->redirect('index');

    }


    /*
     * 相应ajax请求,根据属性组查询对应的属性
     */
    public function attribute(){
        # 接受数组
        $attributeGroupId= input('attribute_group_id');
        $productID = input('productId');
        #查询对应属性组
        $attributeList = Db::name('attribute')
            ->alias('a')
            ->field('pa.id,a.title,pa.value,a.id attribute_id,pa.is_extend is_extend')
            ->join('__PRODUCT_ATTRIBUTE__ pa',"pa.attribute_id = a.id AND pa.product_id='$productID'",'LEFT')
            ->where('a.attribute_group_id',$attributeGroupId)
            ->select();

        return $attributeList;
    }

    /**
     * 图片上传
     */
    public function upload(){
        $request = request();
        # 保存上传文件
        $uploads_path = ROOT_PATH."/public/uploads/";
        $image = $request->file('image-file');
        //文件验证和保存
        $imageInfo = $image->validate(['size'=>1*1024*1024,'ext'=>['jpg','png','gif','jpeg'],'type'=>['image/png,','image/jpg','image/gif','image/jpeg']])
            ->move($uploads_path);

        //判断是否上传成功
        if($imageInfo){
            #处理缩略图
            $thumb_width = getConf('product.thumb_width');
            $thumb_height = getConf('product.thumb_height');

            $thumb_path = ROOT_PATH."/public/thumb/";
            $dir_path = dirname($imageInfo->getSaveName());   //20170906
            $thumb_file_name = "thumb_{$thumb_width}x{$thumb_height}_".$imageInfo->getFilename();
            //确保存储路径存在
            if(!is_dir($thumb_path.$dir_path)){
                mkdir($thumb_path.$dir_path,0777,true);
            }

            $image_thumb = Image::open($image);
            $thumbInfo = $image_thumb->thumb(300,300)->save($thumb_path.$dir_path.'/'.$thumb_file_name);

            if($thumbInfo){
                #响应数据
                return [
                    'error'=>false,
                    'image'=>str_replace('\\','/',$imageInfo->getSaveName()),   //将\替换成/
                    'image_thumb'=>$dir_path.'/'.$thumb_file_name
                ];

            }else{
                return [
                    'error'=>true,
                    'errorInfo'=>$thumbInfo->getError()
                ];
            }

        }else{
            return [
                'error'=>true,
                'errorInfo'=>$image->getError()
            ];
        }
        
    }

}