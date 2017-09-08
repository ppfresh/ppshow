<?php 

namespace app\api\controller;
use think\Controller;
use app\api\model\Category as CategoryModel;

class Category extends Controller{

	/**
	 * 响应前台分类的方法
	 */
	public function ls()
	{
		# 获取参数
		$type = input('type','nested');
		$maxDeep = input('maxDeep',0);
		
		# 调用模型的方法,完成数据的获取
		$list = (new CategoryModel())->getList($type,$maxDeep);
		# 响应
		return [
			'error'	=>	false,
			'type'	=>	$type,
			'maxDeep'=>	$maxDeep,
			'data' 	=> 	$list
		];
	}

}









