<?php

namespace app\admin\controller;

use app\home\model\HpvModel;

class Hpv extends AdminController
{
    private $model_hpv = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_hpv = new HpvModel();
    }
    /**
     * hpv列表
     */
    public function hpv_list()
    {
        $where = [];
        $res = $this->model_hpv->getListInfo($where, [], '');

        return return_info(200,'hpv列表',$res);
    }
    /**
     * 更改hpv预约情况
     */
    public function attendance()
    {
        $hpv_id = input('post.hpv_id');
        $plan_state = input('post.plan_state');//预约情况，1：预约中，2：预约成功，3：预约失败
        $status = input('post.status');//出席状况，1：出席，2：未出席
        $fail_reason = input('post.fail_reason');//失败原因
        if(empty($hpv_id) || empty($plan_state) || empty($status) || empty($status)){
            return return_info();
        }
        $hpv = HpvModel::get($hpv_id);
        if(!$hpv)return return_info(300, '找不到该hpv信息');

        $hpv->plan_state     = $plan_state;
        $hpv->status     = $status;
        $hpv->fail_reason    = $fail_reason;
        $hpv->finish_time    = date('Y-m-d H:i:s');
        if($hpv->save()){
            return return_info(200, '操作成功');
        }else{
            return return_info(300, '操作失败');
        }
    }
}