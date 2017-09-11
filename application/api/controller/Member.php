<?php 
namespace app\api\controller;
use think\Controller;
use app\api\model\Member as MemberModel;

class  Member extends Controller
{
	/**
	 * 添加会员
	 */
	public function add(){
		$data = input();

		$model = new MemberModel();
		$res = $model->allowField(true)->data($data)->save();
		if($res){
			return [
				'error'=>false,
				'id'=>$model->id
			];
		} else {
			return [
				'error'	=> true
			];
		}
	}

	/**
	 * 验证会员登录
	 */
	public function login(){

	}
}
















