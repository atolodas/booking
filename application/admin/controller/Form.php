<?php
namespace app\admin\controller;

use app\home\model\ProductModel;
use app\home\model\ProductTimeModel;
use think\Db;
use think\Controller;
use app\home\model\FormModel;

class Form extends Controller
{
    private $model_form = [];
    public function __construct()
    {
        $this->model_form = new FormModel();
    }
    /**
     * 表单展示数据（废弃）
     */
    public function form_web(){
        $p_id = input('get.p_id');
        if(empty($p_id)){
            return return_info(300);
        }
        $model_product = new ProductModel();
        $model_product_time = new ProductTimeModel();
        $product = $model_product->getInfo([['a.p_id','=',$p_id]],[['bo_hospital b','a.h_id=b.h_id']],'b.h_name,b.h_remark');
        if (!$product)return return_info(300,'该产品出错');
        //获取预约时间和库存信息
        $p_time_arr = $model_product_time->getListInfo([['p_id','=',$p_id]],[],'pt_date,pt_day,pt_stock');
        $product['p_time_arr'] = $p_time_arr;
        //附带
        return return_info(200,'',$product);
    }
    /**
     * 表单管理列表
     */
    public function form_manage(){
        $f_name = input('get.f_name');//姓名
        $f_organization = input('get.f_organization');//预约机构
        $f_order_time_start = input('get.f_order_time_start');    //下单时间
        $f_order_time_end = input('get.f_order_time_end');    //下单时间
        $query_start_time = input('get.query_start_time');    //表单提交时间
        $query_end_time = input('get.query_end_time');    //表单提交时间
        $is_outexcel = input('get.is_outexcel');
        $where = array();
        if($f_name)$where[] = ['f_name','like', '%'.$f_name.'%'];
        if($f_organization)$where[] = ['f_organization','like', '%'.$f_organization.'%'];
        if (!empty($f_order_time_start) || !empty($f_order_time_end)) {//下单时间
            $where[] = get_query_time('f_order_time',$f_order_time_start,$f_order_time_end);
        }
        if (!empty($query_start_time) || !empty($query_end_time)) {//表单提交时间
            $where[] = get_query_time('create_time',$query_start_time,$query_end_time);
        }
        $field = 'f_api,f_order_time,f_organization,f_project,f_collect,f_bring_back,f_shop,f_name,f_pinyin,f_phone,f_sex,f_birthday,f_age,f_weight,f_date,f_time,f_pass_check,f_id_card,f_passport,f_order_sn,f_remark,f_address,create_time';

        if ($is_outexcel == 1) {  //导出
            $list = $this->model_form->getListInfo($where, [], $field, 1);
        } else {
            $field .= ',f_id,p_id';
            $list = $this->model_form->getListPageTotalInfo($where, [], $field, 1);
        }
        foreach ($list as $k=>$v){
        }
//var_dump($list);exit;
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '来源';
            $arr1[] = '下单时间';
            $arr1[] = '预约机构';
            $arr1[] = '预约项目';
            $arr1[] = '到付';
            $arr1[] = '二三针是否带回';
            $arr1[] = '分店';
            $arr1[] = '姓名';
            $arr1[] = '拼音';
            $arr1[] = '手机号码';
            $arr1[] = '性别';
            $arr1[] = '出生日期';
            $arr1[] = '年龄';
            $arr1[] = '体重（KG）';
            $arr1[] = '预约日期';
            $arr1[] = '预约时间';
            $arr1[] = '港澳通行证号码';
            $arr1[] = '身份证号码';
            $arr1[] = '护照';
            $arr1[] = '订单号';
            $arr1[] = '订单备注';
            $arr1[] = '收货地址';
            $arr1[] = '表单提交时间';
            createExcel($arr1, $list, '表单管理列表');
        } else {
            return return_info('200', '表单管理列表', $list);
        }
    }


}