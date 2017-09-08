<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 21:22
 */

namespace app\back\model;
use think\Model;
use think\Session;

class Product extends Model
{
    //protected $table = 'jql_product';
    protected $auto = ['upc','date_available','admin_id'];


    public function setUpcAttr($value){
        return $value? : date('YmdHis').mt_rand(100,999).mt_rand(100,999);
    }

    public function setAdminIdAttr($value){
    	return $value? : Session::get('admin.id');
    }

    public function setDateAvailableAttr($value){
        return $value? : date('Y-m-d H:i:00');
    }
}