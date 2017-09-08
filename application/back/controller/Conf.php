<?php 

namespace app\back\controller;
use think\Controller;

class Conf extends Controller{

	/**
	 * 用户配置管理
	 */
	public function set()
	{
		$request = request();
		# 展示配置模板
		if($request->isGet()){
			$this->assign('configs',getConf());
			return $this->fetch();
		}
		# 修改配置
		elseif($request->isPost()){
			# 接受数据
			$data = input('configs/a',[]);
			$line = '';

			# 将数据拼接成目标字符串,并写入到config.ini文件中
			foreach($data as $section => $config){
				$line .= "[$section]".PHP_EOL;
				foreach($config as $key => $value){
					$line .= "$key = $value".PHP_EOL;
				}
				$line .= PHP_EOL;
			}

			## 写入
			$file = ROOT_PATH.'config.ini';
			$handle = fopen($file,'w');	//覆盖写
			$res = flock($handle,LOCK_EX);
			if($res){
				//上锁成功
				fwrite($handle,$line);
			}
			fclose($handle);	//自动释放锁定

			$this->redirect('set');
		}
	}


}






