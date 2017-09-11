<?php 

namespace app\api\controller;
use think\Controller;
use \cart\Cart as objCart;
use think\Db;

class Cart extends Controller{

	/**
	 * 向购物车添加商品
	 */
	public function add()
	{
		$product_id = input('product_id');
		$buyQuantity = input('buyQuantity');

		$cart = objCart::instance();
		$cart->add($product_id ,$buyQuantity);
		
		return [
			'error' => false,
		];
	}

	/**
	 * 获得迷你购物车商品数据
	 * @return [type] [description]
	 */
	public function info()
	{
		$cart = objCart::instance();
		$productList = $cart->get();
		// dump($productList);die;
		
		if(empty($productList)){
			//购物车中没有商品
			return [
				'error'	=>	false,
				'data'	=>	[
					'productList'=>[],
					'amount'=>0,
					'total'=>0
				]
			];
		}
		$ids = array_keys($productList);
		//查询出商品详细信息
		$query = Db::name('product')->alias('p')
				->join('__PRODUCT_ATTRIBUTE__ pa','pa.product_id = p.id AND pa.is_extend = 1','LEFT')
				->field('p.id ,p.title ,p.price ,p.image_thumb ,group_concat(value) valueList')
				->where('p.id','in',$ids)
				->group('p.id')
				->select();
		
		//生成返回数据
		$amount = $total = 0;
		foreach($query as $key => &$value){
			$value['buyQuantity'] = $productList[$value['id']];
			#单品总价
			$value['amount'] = $value['price'] * $value['buyQuantity'];
			#总金额
			$amount += $value['amount'];
			#总数量
			$total += $value['buyQuantity'];
		}

		return [
			'error'	=>	false,
			'data' => [
				'productList'=>$query,
				'amount' => $amount,
				'total' => $total
			]
		];
	}

	/**
	 * 更新购物车
	 * @return [type] [description]
	 */
	public function update()
	{
		# 获取更新数据
		$product_id = input('product_id');
		$buyQuantity = input('buyQuantity');

		$cart = objCart::instance();
		$cart->update($product_id ,$buyQuantity);
		return [
			'error'=>false
		];
	}

	/**
	 * 移除购物车商品
	 * @return [type] [description]
	 */
	public function remove()
	{
		#获取id
		$product_id = input('product_id');

		$cart = objCart::instance();
		$cart->remove($product_id);

		return [
			'error'=>false
		];
	}
}






