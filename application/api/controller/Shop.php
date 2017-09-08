<?php 

namespace app\api\controller;
use think\Controller;
class Shop extends Controller{

	/**
	 * 商店数据初始化数据接口方法
	 * 该方法响应前端初始化的ajax请求
	 * @return [type] [description]
	 */
	public function init(){
		return [
			'shop'=>[
				'telphone' => getConf('shop.telphone')
			]
		];
	}

} 