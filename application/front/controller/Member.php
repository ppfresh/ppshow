<?php 

namespace app\front\controller;
use think\Controller;
use app\front\validate\Register;
use think\Session;
class Member extends Controller{

	/**
	 * 会员注册
	 * @return [type] [description]
	 */
	public function register(){
		$request = request();
		if($request->isGet()){
			return $this->fetch('register',[
				'errorInfo'=>Session::get('errorInfo') ? : [],
				'data'	=>	Session::get('data') ? : [],
				]);
		} elseif ($request->isPost()){
			//注册
			$data = input();
			//对数据进行校验
			$validate = new Register();
			$res = $validate->batch(true)->check($data);
			if(!$res){
				$this->redirect('register',[],'302',[
					'error'	=>	true,
					'errorInfo'	=>	$validate->getError(),
					'data'	=> $data
					]);
			}
			//校验通过,入库
			//处理密码,生成加盐后的密码
			$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_';
			$data['salt'] = substr(str_shuffle($str), 0, mt_rand(2,12));
			$data['password'] = crypt($data['password'],$data['salt']);
			
			# 关于操作数据库的,统一使用接口来完成
			# 使用curl发送请求,我们将curl发送请求的操作,进行一下封装
			$resp = httpRequest([
				'type'=>'post',
				'data'=>$data,
				'url'=>url('/memberAdd',[],'html',true),	// 请求,参数,后缀,绝对路径
				'dataType'=>'json',
				]);

			if($resp !== false && $resp['error'] == false){
				//会员添加成功
				$this->redirect('login');
			} else {
				$this->redirect('register');
			}	
		 }
	}
	
	/**
	 * 会员登录
	 */
	public function login(){
		$request = request();
		if($request->isGet()){
			return $this->fetch();
		}
	}
	/**
	 * 结算
	 * @return [type] [description]
	 */
	public function checkout(){

	}


}



















