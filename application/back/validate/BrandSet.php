<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/8/28
 * Time: 21:12
 */

namespace app\back\validate;
use think\Validate;

class BrandSet extends Validate
{
    protected $rule = [
        'title' =>  'require|unique:brand,title',//unique必须要指定表
        'site'  =>  'url',
        'sort'  =>  'require|number'
    ];

    protected $field = [
        'title' =>  '品牌',
        'site'  =>  '官网',
        'sort'  =>  '排序'
    ];
}