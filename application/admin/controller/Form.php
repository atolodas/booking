<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;
use app\home\model\FormModel;

class Form extends AdminController
{
    private $model_form = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_form = new FormModel();
    }
    /**
     * 表单管理列表
     */
    public function form_manage(){
        $keyword = input('get.keyword');//姓名/手机号
        $f_organization = input('get.f_organization');//预约机构
        $query_start_time = input('get.query_start_time');    //预约时间
        $query_end_time = input('get.query_end_time');    //预约时间
        $is_outexcel = input('get.is_outexcel');
        $where = array();
        if($keyword)$where[] = ['f_name|f_phone','like', '%'.$keyword.'%'];
        if($f_organization)$where[] = ['f_organization','like', '%'.$f_organization.'%'];
        if (!empty($query_start_time) || !empty($query_end_time)) {//预约时间
            $where[] = getQueryTime('f_date',$query_start_time,$query_end_time);
        }
        $field = 'f_order_sn,f_api,f_organization,f_shop,f_project,f_name,f_pinyin,f_phone,f_sex,f_birthday,f_age,CONCAT(f_date,\' \',f_time) f_date_time,f_id_card,f_pass_check,f_passport,f_city,f_address,create_time';

        if ($is_outexcel == 1) {  //导出
            $list['data'] = $this->model_form->getListInfo($where, [], $field);
        } else {
            $field .= ',f_id';
            $list = $this->model_form->getListPageTotalInfo($where, [], $field);
        }
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['f_address'] = implode(json_decode($v['f_address'],true),' ');
        }
//var_dump($list);exit;
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '订单号';
            $arr1[] = '来源';
            $arr1[] = '预约机构';
            $arr1[] = '分店';
            $arr1[] = '预约项目';
            $arr1[] = '姓名';
            $arr1[] = '拼音';
            $arr1[] = '手机号码';
            $arr1[] = '性别';
            $arr1[] = '出生日期';
            $arr1[] = '年龄';
            $arr1[] = '预约日期时间';
            $arr1[] = '身份证号码';
            $arr1[] = '港澳通行证号码';
            $arr1[] = '护照';
            $arr1[] = '城市';
            $arr1[] = '收货地址';
            $arr1[] = '表单提交时间';
            createExcel($arr1, $list, '表单管理列表');
        } else {
            return return_info('200', '表单管理列表', $list);
        }
    }


}