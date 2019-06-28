<?php

namespace app\home\model;

use think\Db;

class HpvModel extends BookingModel
{
    protected $pk = 'hpv_id';
    protected $table = 'bo_hpv';

    public function getFinishTimeAttr($value){
        return empty($value) ? '' :$value;
    }
    public function get_hpv_num($value){
        $arr = ['未知状态','第一针','第二针','第三针'];
        return isset($arr[$value]) ? $arr[$value] : '';
    }
    public function get_plan_state($value){
        $arr = ['未知状态','预约中','预约成功','预约失败'];
        return isset($arr[$value]) ? $arr[$value] : '';
    }
    public function get_status($value){
        $arr = ['未知状态','等待接种','已接种','未接种'];
        return isset($arr[$value]) ? $arr[$value] : '';
    }
    /**
     * 增加 hpv 预约记录
     * @param string $from_id
     * @param $name
     * @param $phone
     * @param $hpv_num  第几针
     * @param $hpv_date
     * @param $hpv_time
     * @param $plan_state   1：预约中，2：预约成功，3：预约失败
     * @param $status   出席状况，1：等待接种，2：出席（已接种），3：未出席（未接种）
     */
    public function add_hpv($form_id='',$f_order_sn,$name,$phone,$hpv_num,$hpv_date=null,$hpv_time='',$plan_state=1,$status=1){
        $this->form_id = $form_id;
        $this->f_name = $name;
        $this->f_order_sn = $f_order_sn;
        $this->f_phone = $phone;
        $this->hpv_num = $hpv_num;
        $this->hpv_date = $hpv_date;
        $this->hpv_time = $hpv_time;
        $this->plan_state = $plan_state;
        $this->status = $status;
        $this->save();
        return $this->hpv_id;
//        echo Db::getLastSql();
    }
}