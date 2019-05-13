<?php

namespace app\home\model;

use think\Db;

class HpvModel extends BookingModel
{
    protected $pk = 'hpv_id';
    protected $table = 'bo_hpv';

    /**
     * 增加 hpv 预约记录
     * @param string $from_id
     * @param $name
     * @param $phone
     * @param $hpv_num  第几针
     * @param $hpv_date
     * @param $hpv_time
     * @param $status   0：未预约，1：预约中，2：预约成功，3：预约失败
     */
    public function add_hpv($name,$phone,$hpv_num,$hpv_date,$hpv_time,$status=0,$from_id=''){
        $this->from_id = $from_id;
        $this->f_name = $name;
        $this->f_phone = $phone;
        $this->hpv_num = $hpv_num;
        $this->hpv_date = $hpv_date;
        $this->hpv_time = $hpv_time;
        $this->status = $status;
        $this->save();
//        echo Db::getLastSql();
    }
}