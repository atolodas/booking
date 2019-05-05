<?php
namespace app\home\model;

class FormModel extends BookingModel
{
    protected $pk = 'f_id';
    protected $table = 'bo_form';

    public function getFSexAttr($value){
        $arr = ['未知','男','女'];
        if(isset($arr[$value])){
            return $arr[$value];
        }else{
            return $value;
        }
    }
    public function getFApiAttr($value){
        $arr = ['youzan'=>'有赞商城'];
        if(isset($arr[$value])){
            return $arr[$value];
        }else{
            return $value;
        }
    }
}