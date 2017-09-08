<?php 

namespace app\behavior;

class ConfigParse{

	/**
	 * 用户配置文件解析
	 */
	public function run(){
		$file = ROOT_PATH.'config.ini';
		$GLOBALS['config'] = parse_ini_file($file,true);
	}


}











