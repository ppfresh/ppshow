<?php 

namespace app\back\controller;
use think\Config;
use think\Controller;
use think\Db;

class Make extends Controller
{
	/**
	 * 脚手架生成信息页面
	 */
	public function table()
	{
		return $this->fetch();
	}

	/**
	 * 通过ajax获取信息
	 */
	public function info()
	{
		# 得到表名
		$table = input('table');

		# 获取coment属性
		$database = Config::get('database.database');
		$table_prefix = Config::get('database.prefix');
		$table = $table_prefix.$table;

		$sql = "select table_comment from information_schema.tables where table_schema = ? and table_name=?";
		$rows = Db::query($sql,[$database,$table]); //二维数组

		# 获得字段信息
		$sql = "select column_name,column_comment from information_schema.columns where table_schema = ? and table_name = ?";
		$fields = Db::query($sql,[$database,$table]);

		return [
		    'title' => $rows[0]['table_comment'],
		    'fields'=> $fields
        ];
	}

    /**
     * 自动生成
     */
	public function generate()
    {
        $table = input('table');
        $title = input('title');
        $fields = input('fields/a');

        //生成控制器
        $this->generateController($table,$title);

       //生成验证类
       $this->generateValidate($table);

       //生成模型
       $this->generateModel($table);

       //生成index视图
       $this->generateIndex($table,$title,$fields);

       //生成set视图
       $this->generateSet($table,$title,$fields);

    }

    /**
     * 生成控制器方法
     * @param $table
     * @param $title
     */
    public function generateController($table,$title)
    {
        # 获得%key%
        $key = $this->tableKey($table);

        #组成替换条件
        $search = ['%key%','%title%'];
        $replace = [$key,$title];
        $template = APP_PATH.'back/codeTemplate/controller.tpl';
        $file = APP_PATH.'back/controller/'.$key.'.php';

        #替换
        $res = $this->replace($search,$replace,$template,$file);
        echo '控制器'.$key.($res ? '生成成功!<br>':'生成失败!<br>');
    }

    /**
     * 生成验证器方法
     * @param $table
     */
    public function generateValidate($table)
    {
    	$key = $this->tableKey($table);

    	$search = ['%key%'];
    	$replace = [$key];
    	$template = APP_PATH.'back/codeTemplate/validate.tpl';
    	$file = APP_PATH.'back/validate/'.$key.'Set.php';

    	$res = $this->replace($search,$replace,$template,$file);
    	echo '验证器'.$key.($res?'生成成功<br>':'生成失败<br>');
    }

    /**
     * 生成模型的方法
     * @param $table
     */
    public function generateModel($table)
    {
        //获得%key%
        $key = $this->tableKey($table);

        //组成替换条件
        $search= ['%key%','%table%'];
        $replace = [$key,$table];
        $template = APP_PATH.'back/codeTemplate/model.tpl';
        $file = APP_PATH.'back/model/'.$key.'.php';

        //替换模板,生成模型
        $res = $this->replace($search,$replace,$template,$file);
        echo '模板'.$key.($res ? '生成成功!<br>':'生成失败!<br>');
        
    }

    /**
     * 生成index视图的方法
     * @param   $table  
     * @param   $title  
     * @param   $fields 
     * @return          
     */
    public function generateIndex($table,$title,$fields)
    {
    	//dump($fields);die;
    	# 获得%key%
    	$key = $this->tableKey($table);

    	# 组织index-head-list和index-body-list
    	$indexHeadStr = '';
    	$indexBodyStr = '';
    	foreach($fields as $field){
    		//判断是否需要在列表中显示
    		if(!isset($field['is_index'])) continue;

    		//确定字段替换数据
    		$search = ['%field_name%','%field_title%'];
    		$replace = [$field['field'],$field['title']];

    		//判断是否需要排序
    		if(!isset($field['is_sort'])){
    			$template = APP_PATH.'back/codeTemplate/index-head.tpl';
    		}else{
    			$template = APP_PATH.'back/codeTemplate/index-head.order.tpl';
    		}
    		//替换head数据
    		$indexHeadStr .= str_replace($search,$replace,file_get_contents($template));

    		//替换body数据
    		$template = APP_PATH.'back/codeTemplate/index-body.tpl';
    		$indexBodyStr .= str_replace($search,$replace,file_get_contents($template));
    	}

    	# 替换模板,生成视图
    	$search = ['%title%','%index-head-list%','%index-body-list%'];
    	$replace = [$title,$indexHeadStr,$indexBodyStr];
    	$template = APP_PATH.'back/codeTemplate/index.tpl';
    	$file = APP_PATH.'back/view/'.$table.'/index.html';

    	$res = $this->replace($search,$replace,$template,$file);
    	echo '视图index'.($res?'生成成功!':'生成失败!').'<br>';	
    }

    /**
     * 生成set视图的方法
     * @param  [type] $table  [description]
     * @param  [type] $title  [description]
     * @param  [type] $fields [description]
     * @return [type]         [description]
     */
    public function generateSet($table,$title,$fields)
    {
    	
    	$setBodyStr = '';

    	foreach($fields as $field){

    		//判断该字段是否可以显示
    		if(!isset($field['is_set'])) continue;
    		#组织替换字模板数据
	    	$search = ['%field_name%','%field_title%','%required%'];
	    	$replace = [$field['field'],$field['title'],(isset($field['is_required'])? 'required': '')];
	    	$template = APP_PATH.'back/codeTemplate/set-body.tpl';
	    	$setBodyStr .= str_replace($search,$replace,file_get_contents($template));
    	}
    	

    	//组织主模板数据
    	$search = ['%title%','%set-body-list%'];
    	$replace = [$title,$setBodyStr];
    	$template = APP_PATH.'back/codeTemplate/set.tpl';
    	$file = APP_PATH.'back/view/'.$table.'/set.html';
    	
    	#数据替换
    	$res = $this->replace($search,$replace,$template,$file);
    	echo '视图set'.($res?'生成成功!':'生成失败!').'<br>';
    }

    /**
     * 根据表名生成Key值的方法
     * @param $table
     */
    public function tableKey($table){
        return implode(array_map('ucfirst',explode('_',$table)));
    }
    /**
     * 内容替换方法
     * @param $search
     * @param $replace
     * @param $template
     * @param $file
     */
    public function replace($search,$replace,$template,$file)
    {
        $content = file_get_contents($template);

        $content= str_replace($search,$replace,$content);

        //保证文件目录存在
        $path = dirname($file);
        if(!is_dir($path)){
        	mkdir($path,0777,true);
        }

        $res = file_put_contents($file,$content);

        return $res;
    }
}






