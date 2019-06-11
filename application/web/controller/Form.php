<?php

namespace app\web\controller;

use app\home\model\FormModel;
use app\home\model\HpvModel;
use app\home\model\OrderModel;
use app\home\model\ProductModel;
use app\home\model\ProductTimeModel;
use app\home\logic\Sms as logic_sms;
use app\member\model\MemberTokenModel;
use think\captcha\Captcha;
use think\Db;
use app\lib\Pinyin;
use app\lib\Idcard;
use app\lib\open\Youzan;

class form extends FormController
{
    private $model_form = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_form = new FormModel();
    }
    /**
     * 登录预约系统
     */
    public function login_form()
    {
        $sl_phone = input('post.phone');
        $captcha_code = input('post.captcha');//图形验证码
        $sl_id = input('post.sl_id');//手机验证码id
        $sl_code = input('post.sl_code');//手机验证码
        if(empty($sl_phone) || empty($sl_id) || empty($sl_code)){
            return return_info();
        }
        //检查是否需要开启图形验证码
        $logic_sms = new logic_sms;
        $is_captcha = $logic_sms->check_captcha($sl_phone);
        if($is_captcha)
        {
            if(empty($captcha_code))
            {
                return return_info(300,'图形验证码');
            }
            // 检查图形验证码
            $captcha = new Captcha();
            if( !$captcha->check($captcha_code))
            {
                return return_info(300,'图形验证码错误');
            }
        }
        //检查手机验证码
        $code_info = $logic_sms->sms_check($sl_id,$sl_phone,$sl_code);
        if (isset($code_info['code']) && $code_info['code'] != 200) {
            return $code_info;
        }
        //检查是否有在平台上存在订单
        $model_order = new OrderModel();
        $con = [];
        $con[] = ['buyer_messages|address_info|buyer_words|seller_memo','like', '%'.$sl_phone.'%'];;
        $con[] = ['status','in',['TRADE_PAID','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_SUCCESS']];//已支付,待发货,已发货,已完成
        $order_list = $model_order->getListInfo($con,[],'order_sn,title');
//        echo Db::getLastSql();
        if(count($order_list) < 1){
            return return_info(300,'未查询到您的订单信息');
        }
        $arr = [];
        foreach ($order_list as $k=>$v){
            $v['area'] = '';   //地区
            $v['organization'] = $this->get_organization($v['title']);   //预约机构
            $v['project'] = $this->get_project($v['title']);   //预约服务
            //检查是否预约过   根据
            $is_form = $this->model_form->getInfo([['f_order_sn','=',$v['order_sn']]],[],'f_status');
            $v['status'] = empty($is_form) ? 0 : $is_form['f_status']; //0未预约，1预约成功，2预约失败
            unset($v['title']);
            $arr[$k] = $v;
        }
        $model_member_token = new MemberTokenModel();
        $token = $model_member_token->save_token($sl_id,$sl_phone,$sl_id.$model_member_token->form_stype,$model_member_token->form_stype);

        return return_info(200,'登录成功',['token'=>$token,'order_list'=>$arr]);
    }
    /**
     * 开始预约，第一步
     */
//    public function form_add(){
//
//        $post_error = parameter_check(['f_shop','f_name','f_phone','f_weight','f_date','f_time','f_pass_check','f_id_card','f_order_sn','f_address'],1);
//        if($post_error['code'] == 300){
//            return $post_error;
//        }
//        $data = $post_error['data'];
//        $data['f_api'] = '有赞';//来源
//        $data['f_passport'] = input('post.f_passport');//护照（备选填写）
//        $data['f_remark'] = input('post.f_remark'); //备注
//        $data['f_bring_back'] = input('post.f_bring_back'); //二三针是否带回
//
//        //检查有赞的平台过来的订单，获取订单中的信息
//        $youzan = new Youzan();
//        $order_detail = $youzan->youzan_order_detail($data['f_order_sn']);
//        if($order_detail['code'] !== 200)return return_info(300, '未找到该订单');
//        $order_detail = $order_detail['data'];
//        //检查订单是否退款,是否付款
//        $order_info = $order_detail['full_order_info']['order_info'];   //交易明细详情
//        $status = $order_info['status'];    //订单状态
//        $refund_state = $order_info['refund_state'];    //退款状态 0:未退款; 1:部分退款中; 2:部分退款成功; 11:全额退款中; 12:全额退款成功
//        if($status == 'WAIT_BUYER_PAY')return return_info(300,'订单未付款');//待付款，不允许操作
//        if($status == 'WAIT_CONFIRM')return return_info(300,'订单待确定');//待确定待成团，不允许操作
//        if($refund_state == 11)return return_info(300,'订单退款中');//全额退款中
//        if($refund_state == 12)return return_info(300,'该订单已退款');//全额退款成功
//
//        $orders = $order_detail['full_order_info']['orders'][0];    //订单明细结构体
//        $data['f_order_time'] = $order_info['created'];    //下单时间
//        $num = $orders['num'];//订单中商品数量
//        //检查是是否已经预约
//        $count = $this->model_form->where([['f_order_sn','=',$data['f_order_sn']]])->value('count(*)');//已经约了几次
//        if($count >= $num){
//            return return_info(300, '该订单名额已约满');
//        }
//        $title = $orders['title'];
////        $buyer_messages = $orders['buyer_messages'];
//        //预约机构
//        preg_match_all('/【(.*?)】/',$title,$result);
//        $data['f_organization'] = $result[1][0];
//        $f_project = explode(' ',$title);
//        //预约项目
//        $data['f_project'] = $f_project[0];
//        //是否为HPV预约
//        if(stripos($data['f_project'],'hpv') !== false){
//            $data['f_type'] = 1;//1:hpv
//        }
////        //二三针是否带回
////        $buyer_messages = json_decode($buyer_messages,true);
////        $data['f_bring_back'] = isset($buyer_messages['是否带回']) ? $buyer_messages['是否带回'] : '';
//        //到付 暂未定规则
//        $f_collect = explode('|',$title);
//        $data['f_collect'] = isset($f_collect[1]) ? $f_collect[1] : '';
//
//        //根据姓名得到拼音
//        $pinyin_obj = new Pinyin();
//        $data['f_pinyin'] = $pinyin_obj->encode($data['f_name'],'all');//拼音全拼
//        //根据身份证号码得到 出生日期 性别 年龄
//        $idcard_obj = new Idcard();
//        $f_id_card = $data['f_id_card'];
//        //验证身份证号码的正确性
//        if(!$idcard_obj->isIdCard($f_id_card))return return_info(300,'身份证号码错误');
//        $data['f_birthday'] = $idcard_obj->get_birthday($f_id_card);   //出生日期
//        $data['f_age'] = $idcard_obj->get_age($f_id_card);   //年龄
//        $data['f_sex'] = (string)$idcard_obj->get_sex($f_id_card);   //性别
////        var_dump($data['f_sex']);exit;
//        $res = $this->model_form->allowField(true)->save($data);
//        if($res){
//            //是否为HPV预约
//            if(stripos($data['f_project'],'hpv') !== false){
//                $model_hpv = new HpvModel();
//                $model_hpv->add_hpv($data['f_name'],$data['f_phone'],1,$data['f_date'],$data['f_time'],1,$this->model_form->f_id);
//            }
//            return return_info(200,'操作成功');
//        }else{
//            return return_info(300,'操作失败');
//        }
//    }
    public function make_appointment()
    {
        $order_sn = input('post.order_sn');
        if(empty($order_sn))return return_info();

        $post_error = parameter_check(['f_name','f_id_card','f_wx_number','f_pass_check','f_city'],1);
        if($post_error['code'] == 300){
            return $post_error;
        }
        $data = $post_error['data'];
        $data['f_order_sn'] = $order_sn;
        $data['f_phone'] = $this->phone;
        $data['f_api'] = 'youzan';//来源
        $data['f_passport'] = input('post.f_passport');//护照（备选填写）
        $data['f_address'] = input('post.f_address');//收货地址

        //检查有赞的平台过来的订单，获取订单中的信息
        $youzan = new Youzan();
        $order_detail = $youzan->youzan_order_detail($order_sn);
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
        $count = $this->model_form->where([['f_order_sn','=',$order_sn]])->count();//已经约了几次
        if($count >= $num){
            return return_info(300, '该订单名额已约满');
        }
        $title = $orders['title'];
//        $buyer_messages = $orders['buyer_messages'];
        //预约机构
        $data['f_organization'] = $this->get_organization($title);
        //预约项目
        $data['f_project'] = $this->get_project($title);
        //是否为HPV预约
        if(stripos($data['f_project'],'hpv') !== false && stripos($data['f_project'],'改签') === false){
            $data['f_type'] = 1;//1:hpv
        }
//        //二三针是否带回
//        $buyer_messages = json_decode($buyer_messages,true);
//        $data['f_bring_back'] = isset($buyer_messages['是否带回']) ? $buyer_messages['是否带回'] : '';
        //到付 暂未定规则
        $data['f_collect'] = $this->get_collect($title);

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
        $res = $this->model_form->allowField(true)->save($data);
        if($res){
            return return_info(200,'操作成功',['form_id'=>$this->model_form->f_id]);
        }else{
            return return_info(300,'操作失败');
        }
    }
    /**
     * （废弃）根据订单号获取信息
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
     * 预约日期和时间，第二步
     */
    public function appointment_date()
    {
        $model_hpv = new HpvModel();
        try{
            $post_error = parameter_check(['f_id','type','f_date','f_time'],1);
            if($post_error['code'] == 300){
                throw new \Exception($post_error['message']);
            }
            $data = $post_error['data'];
            $form_info = $this->model_form->getInfo([['f_id','=',$data['f_id']]]);
            if(!$form_info)throw new \Exception('找不到该表单');

            Db::startTrans();
            //是否为HPV预约
            if(stripos($form_info['f_project'],'hpv') !== false){
                //检查前面的针剂是否已经接种成功
//                if($data['type'] > 1){
//
//                }
                //检查是否已经存在记录
                $hpv = $model_hpv->getInfo([['from_id','=',$data['f_id']],['hpv_num','=',$data['type']]]);
                if($hpv){
                    $hpv_data['hpv_date'] = $data['f_date'];
                    $hpv_data['hpv_time'] = $data['f_time'];
                    $model_hpv->save($hpv_data,[['hpv_id','=',$hpv['hpv_id']]]);
                }else{
                    //增加HPV记录
                    $res = $model_hpv->add_hpv($data['f_id'], $form_info['f_name'], $form_info['f_phone'], $data['type'], $data['f_date'], $data['f_time']);
                    if(!$res){
                        throw new \Exception('1错误');
                    }
                }
            }
            if($data['type'] == 1){
                //插入数据
                $res = $this->model_form->allowField(['f_date','f_time'])->save($data,[['f_id','=',$data['f_id']]]);
                if(!$res)throw new \Exception('错误');
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $err_arr = return_info(2000,$e->getMessage().$e->getLine());
            return $err_arr;
        }

        return return_info(200,'操作成功');
    }


}