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
        $from_id = input('get.from_id');
        $order_sn = input('get.order_sn'); //
        $hpv_num = input('get.hpv_num'); //第几针
        $hpv_date = input('get.hpv_date'); //预约时间
        $f_value = input('get.f_value'); //姓名/手机号
        $where = [];
        if(!empty($from_id)){
            $where[] = ['from_id','=',$from_id];
        }
        if(!empty($order_sn)){
            $where[] = ['f_order_sn','like','%'.$order_sn.'%'];
        }
        if(!empty($hpv_num)){
            $where[] = ['hpv_num','=',$hpv_num];
        }
        if(!empty($hpv_date)){
            $where[] = ['hpv_date','=',$hpv_date];
        }
        if(!empty($f_value)){
            $where[] = ['f_name|f_phone','like','%'.$f_value.'%'];
        }

        $res = $this->model_hpv->getListInfo($where, [], 'hpv_id,hpv_num,f_order_sn,f_name,f_phone,CONCAT(hpv_date,\' \',hpv_time) hpv_date_time,plan_state,status,finish_time,fail_reason');
        foreach ($res as $k=>$v){
            $res[$k]['plan_state'] = $this->model_hpv->get_plan_state($v['plan_state']);
            $res[$k]['status'] = $this->model_hpv->get_status($v['status']);
            $res[$k]['hpv_num'] = $this->model_hpv->get_hpv_num($v['hpv_num']);
        }
        return return_info(200,'hpv列表',$res);
    }
    /**
     * 更改hpv预约情况
     */
    public function attendance()
    {
        $hpv_id = input('post.hpv_id');
        $plan_state = input('post.plan_state');//预约情况，1：预约中，2：预约成功，3：预约失败
        $status = input('post.status');//出席状况，1：等待接种，2：出席（已接种），3：未出席（未接种）
        $fail_reason = input('post.fail_reason');//失败原因
        if(empty($hpv_id) || empty($plan_state) || empty($status)){
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