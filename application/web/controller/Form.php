<?php

namespace app\web\controller;

use app\home\model\ProductModel;
use app\home\model\ProductTimeModel;
use think\Db;
use think\Controller;
use app\lib\Pinyin;
use app\lib\Idcard;
use app\lib\open\Youzan;

class form extends Controller
{
    /**
     * 添加表单
     */
    public function form_add(){

        $post_error = parameter_check(['f_shop','f_name','f_phone','f_weight','f_date','f_time','f_pass_check','f_id_card','f_order_sn','f_address'],1);
        if($post_error['code'] == 300){
            return $post_error;
        }
        $data = $post_error['data'];
        $data['f_api'] = '有赞';//来源
        $data['f_passport'] = input('post.f_passport');//护照（备选填写）
        $data['f_remark'] = input('post.f_remark'); //备注
        $data['f_bring_back'] = input('post.f_bring_back'); //二三针是否带回

        //检查有赞的平台过来的订单，获取订单中的信息
        $youzan = new Youzan();
        $order_detail = $youzan->youzan_order_detail($data['f_order_sn']);
        if($order_detail['code'] !== 200)return return_info(300, '未找到该订单');
        $order_detail = $order_detail['data'];
        //检查订单是否退款,是否付款
        $order_info = $order_detail['full_order_info']['order_info'];   //交易明细详情
        $status = $order_info['status'];    //订单状态
        $refund_state = $order_info['refund_state'];    //退款状态 0:未退款; 1:部分退款中; 2:部分退款成功; 11:全额退款中; 12:全额退款成功
        if($status == 'WAIT_BUYER_PAY')return return_info(300,'订单未付款');//待付款，不允许操作
        if($status == 'WAIT_CONFIRM')return return_info(300,'订单待确定');//待确定待成团，不允许操作
        if($refund_state == 11)return return_info(300,'订单退款中');//全额退款中
        if($refund_state == 12)return return_info(300,'该订单已退款');//全额退款成功

        $orders = $order_detail['full_order_info']['orders'][0];    //订单明细结构体
        $data['f_order_time'] = $order_info['created'];    //下单时间
        $num = $orders['num'];//订单中商品数量
        //检查是是否已经预约
        $count = $this->model_form->where([['f_order_sn','=',$data['f_order_sn']]])->value('count(*)');//已经约了几次
        if($count >= $num){
            return return_info(300, '该订单名额已约满');
        }
        $title = $orders['title'];
//        $buyer_messages = $orders['buyer_messages'];
        //预约机构
        preg_match_all('/【(.*?)】/',$title,$result);
        $data['f_organization'] = $result[1][0];
        $f_project = explode(' ',$title);
        //预约项目
        $data['f_project'] = $f_project[0];
//        //二三针是否带回
//        $buyer_messages = json_decode($buyer_messages,true);
//        $data['f_bring_back'] = isset($buyer_messages['是否带回']) ? $buyer_messages['是否带回'] : '';
        //到付 暂未定规则
        $f_collect = explode('|',$title);
        $data['f_collect'] = isset($f_collect[1]) ? $f_collect[1] : '';

        //根据姓名得到拼音
        $pinyin_obj = new Pinyin();
        $data['f_pinyin'] = $pinyin_obj->encode($data['f_name'],'all');//拼音全拼
        //根据身份证号码得到 出生日期 性别 年龄
        $idcard_obj = new Idcard();
        $f_id_card = $data['f_id_card'];
        //验证身份证号码的正确性
        if(!$idcard_obj->isIdCard($f_id_card))return return_info(300,'身份证号码错误');
        $data['f_birthday'] = $idcard_obj->get_birthday($f_id_card);   //出生日期
        $data['f_age'] = $idcard_obj->get_age($f_id_card);   //年龄
        $data['f_sex'] = (string)$idcard_obj->get_sex($f_id_card);   //性别
//        var_dump($data['f_sex']);exit;
        $res = $this->model_form->allowField(true)->save($data);
        if($res){
            return return_info(200,'操作成功');
        }else{
            return return_info(300,'操作失败');
        }
    }
    /**
     * 根据订单号获取信息
     */
    public function get_bring_back(){
        $model_product = new ProductModel();
        $model_product_time = new ProductTimeModel();
        $order_sn = input('get.f_order_sn');
        if(empty($order_sn)){
            return return_info(300);
        }
        //检查有赞的平台过来的订单，获取订单中的信息
        $youzan = new Youzan();
        $order_detail = $youzan->youzan_order_detail($order_sn);
        if($order_detail['code'] !== 200)return return_info(300, '未找到该订单');
        $order_detail = $order_detail['data'];
        //根据标题获取是否有第二针带回信息
        $orders = $order_detail['full_order_info']['orders'][0];    //订单中多个商品信息
        $title = $orders['title'];
        $arr['f_bring_back'] = substr_count($title,'二三针') > 0 ? 1 : 0;//0不显示，1显示
        //根据订单号找到对应的产品，展示产品可预约时间（有赞商城的产品名称和预约系统的产品名称需一致）
        $title = trim($title);
        $product = $model_product->getInfo([['a.p_name','=',$title]],[['bo_hospital b','a.h_id=b.h_id']],'a.p_id,b.h_name,b.h_remark');
//        echo Db::getLastSql();
        if (!$product)return return_info(300,'该产品出错');
        //获取预约时间和库存信息
        $p_time_arr = $model_product_time->getListInfo([['p_id','=',$product['p_id']]],[],'pt_date,pt_day,pt_stock');
        $arr['h_remark'] = $product['h_remark'];
        $arr['p_time_arr'] = $p_time_arr;

        return return_info(200, '成功',$arr);
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
}