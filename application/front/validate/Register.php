<?php 

namespace app\front\validate;
use think\Validate;

class Register extends Validate{

	protected $rule =[

		'email'	=>	'require|email|unique:member,email',	//必填,邮箱,唯一:表名,字段
		'password'	=>	'require',
		'confirm'	=>	'require|confirm:password',
		'agree'	=>	'require'
	];

	protected $field =[
		'email'	=>	'邮箱',
		'password'	=>	'密码',
		'confirm'	=>	'确认密码',
		'agree'	=>	'同意隐私政策'	

	];
}

















