<?php 

namespace app\api\controller;
use think\Controller;
use think\Db;

class Product extends Controller{

	public function ls(){

		#接受参数
		##类型
		$type = input('type','new');
		##限定
		$offset = input('offset',0);
		$limit = input('limit',4);

		#获取商品列表数据
		$model  = Db::name('product')->alias('p');
		//and pa.is_extend=1 保证值属性差异的商品可以按差异属性显示
		$model->join('__PRODUCT_ATTRIBUTE__ pa','pa.product_id=p.id AND pa.is_extend=1','LEFT');
		$model->field('p.*,group_concat(pa.value) valueList');
		$model->group('p.id');

		$model->where([
			'p.is_sale' => 1,
			'p.is_delete' => 0
			]);
		#根据请求类型,返回对应数据
		switch($type){
			case 'new':
			$model->order('p.create_time desc');
			break;
		}
		#公共的查询拼凑
		$model->limit($offset,$limit);
		#查询数据
		$rows = $model->select();
		

		#响应数据
		return [
			"error"	=>	false,
			'type'	=>	$type,
			'offset'	=>	$offset,
			'limit'	=>	$limit,
			'data'	=> $rows
		];
		


	}

}




