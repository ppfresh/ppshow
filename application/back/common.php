<?php
/**
 *  实现url附带参数的定向
 * @param string $url 访问的url地址
 * @param string $current 待排序的字段
 * @param string $sort_field 当前排序字段
 * @param string $sort_type 当前排序字段的排序类型
 * @return string url地址
 *
 */
    function urlOrder($url ,$param=[] ,$current ,$field=null ,$type='asc'){

        $param['order_type'] = ($current == $field && $type == 'asc')? 'desc':'asc';
        $param['order_field'] = $current;

        return url($url,$param);
    }

/**
 *  确定排序后class的样式
 * @param string $current 待排序的字段
 * @param string $sort_field 当前排序字段
 * @param string $sort_type 当前排序字段的排序类型
 */
    function classOrder($current ,$field ,$type){
       return ($current == $field)? $type : '' ;
    }













