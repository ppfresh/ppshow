<?php 

namespace app\api\model;
use think\Model;
use think\Cache;


class Category extends Model{

	const CACHE_TREE = 'category_tree';
	const CACHE_NESTED = 'category_nested';

	public function getList($type = 'nested',$maxDeep = 0){
		//因为查询结果复杂,可以考虑使用缓存
		$key = $type == 'nested'?(self::CACHE_NESTED.'_'.$maxDeep):self::CACHE_TREE;
		$list = Cache::get($key);
		if(!$list){//不存在缓存
			
			# 查询出所有分类信息
			$rows = $this->where('is_used','1')->select();
			
			# 递归处理数据
			if($type == 'nested'){
				## 嵌套数据
				$list = $this->nested($rows,0,0,$maxDeep);
				Cache::set($key.'_'.$maxDeep,$list);

				
			}elseif($type == 'tree'){
				## 树状数据
				$list = $this->tree($rows,0,0,$maxDeep);
				Cache::set($key.'_'.$maxDeep,$list);
			}	
		}

		return $list;
		
	}

	/*
	*递归获得树状结构
	* id=0代表顶级分类开始
	* $deep当前的深度
	* $maxDeep最大的深度
	 */
	protected function tree($rows ,$id=0 ,$deep=0 ,$maxDeep=0)
	{
		static $tree = [];
		foreach($rows as $row){
			$row['parent_id'] = $id;
			$row['deep'] = $deep;
			$tree[] = $row;
			if($maxDeep != 0 && $deep < $maxDeep - 1){
				$this->tree($rows,$row['id'],1+$deep,$maxDeep);
			}
			
		}
		return $tree;
	}

	/**
	*递归获得嵌套结构
	*$id=0顶级分类
	 */
	protected function nested($rows ,$id=0 ,$deep=0 ,$maxDeep=0)
	{
		$children = [];
		foreach($rows as $row){
			if($row['parent_id'] == $id){
				if($maxDeep != 0 && $deep < $maxDeep - 1){
					# 说明是子分类:应该保存到父级分类的children:[]中
					$row['children'] = $this->nested($rows ,$row['id'],1+$deep,$maxDeep);
				}
				# 记录分类
				$children[] = $row;
			}
		}
		return $children;
	}
}





