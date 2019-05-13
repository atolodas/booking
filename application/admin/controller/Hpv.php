<?php

namespace app\admin\controller;

use app\home\model\HpvModel;

class Hpv extends AdminController
{
    public function get_hpv(){
        $data = ['f_name'=>'name','f_phone'=>'13112345678','f_date'=>'2019-05-10','f_time'=>'15:45:00'];
        $model_hpv = new HpvModel();
        $model_hpv->add_hpv($data['f_name'],$data['f_phone'],1,$data['f_date'],$data['f_time'],1);

    }
}