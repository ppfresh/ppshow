<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 21:22
 */

namespace app\back\Model;
use think\Cache;
use think\Model;

class Category extends Model
{
    //protected $table = 'jql_category';

    const CACHE_TREE = 'category_tree';
    /**
     * 获取树状结构的商品分类
     */
    public function getTree(){

        $tree = Cache::get(self::CACHE_TREE);
        if(!$tree){

            //查询数据,生成有层次的树
            $rows = $this->order('sort')->select();
            $tree = $this->tree($rows,0,0);

            #   保存到缓存
            Cache::set(self::CACHE_TREE,$tree);
        }

        return $tree;
    }

    /**
     * 递归生成新的排序树
     * @param $rows
     * @param int $parent_id
     * @param int $deep
     * @return array
     */
    protected function tree($rows ,$parent_id = 0 ,$deep = 0){
        static $tree = [];
        foreach($rows as $row){
            if($row['parent_id'] == $parent_id){
                //说明是子分类
                $row['deep'] = $deep;
                $tree[] = $row;
                $this->tree($rows,$row['id'],1+$deep);
            }
        }
        return $tree;
    }

    /**
     * 清除缓存的方法
     */
    public function clearCache(){
        Cache::rm(self::CACHE_TREE);
        return $this;
    }

}