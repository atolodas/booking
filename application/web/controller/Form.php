<?php

namespace app\web\controller;

use app\home\model\FormModel;
use app\home\model\HospitalModel;
use app\home\model\HpvModel;
use app\home\model\OrderModel;
use app\home\model\ProductModel;
use app\home\model\ProductTimeModel;
use app\home\logic\Sms as logic_sms;
use app\lib\Log;
use app\member\model\MemberTokenModel;
use think\captcha\Captcha;
use think\Db;
use app\lib\Pinyin;
use app\lib\Idcard;
use app\lib\open\Youzan;
use think\Exception;

class form extends FormController
{
    private $model_form = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_form = new FormModel();
//        $this->phone = '18777723140';
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
        $is_captcha = $logic_sms->check_captcha($sl_phone,1);
        if($is_captcha)
        {
            if(empty($captcha_code))
            {
                return return_info(300,'请输入图形验证码');
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
        $model_member_token = new MemberTokenModel();
        $token = $model_member_token->save_token($sl_id,$sl_phone,$sl_id.$model_member_token->form_stype,$model_member_token->form_stype);

        return return_info(200,'登录成功',['token'=>$token]);
    }
    /**
     * 订单列表
     */
    public function get_orders(){
        //检查是否有在平台上存在订单
        $model_order = new OrderModel();
        $con = [];
        $con[] = ['buyer_messages|address_info|buyer_words|seller_memo','like', '%'.$this->phone.'%'];
        $con[] = ['status','in',['TRADE_PAID','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_SUCCESS']];//已支付,待发货,已发货,已完成
        $order_list = $model_order->getListInfo($con,[],'order_sn,title');
//        echo Db::getLastSql();
        if(count($order_list) < 1){
            return return_info(300,'未查询到您的订单信息');
        }
        $arr = [];
        foreach ($order_list as $k=>$v){
            //检查是否可以展示，退款的推送文档没有很详细，所以没有做退款的状态更新
//            $check_order_res = $this->check_order($v['order_sn']);
//            if($check_order_res['code'] != 200){
//                continue;
//            }
            $v['organization'] = $this->get_organization($v['title']);   //预约机构
            $h_name = HospitalModel::where([['h_name','=',$v['organization']]])->value('h_area');   //地区
            $v['area'] = $h_name ? $h_name : '';   //地区
            $v['project'] = $this->get_project($v['title']);   //预约服务
            //检查是否预约过   根据
            $is_form = $this->model_form->getInfo([['f_order_sn','=',$v['order_sn']]],[],'f_status');
            $v['status'] = empty($is_form) ? 0 : $is_form['f_status']; //0未预约，1预约成功，2预约失败
            unset($v['title']);
            $arr[] = $v;
        }
        return return_info(200,'订单列表',$arr);
    }
    public function check_order($order_sn){
        //检查有赞的平台过来的订单，获取订单中的信息
        $youzan = new Youzan();
        $order_detail = $youzan->youzan_order_detail($order_sn);
        if($order_detail['code'] !== 200)return return_info(300, '未找到该订单');
        $order_detail = $order_detail['data'];
//        var_dump($order_detail);
        //检查订单是否退款,是否付款
        $order_info = $order_detail['full_order_info']['order_info'];   //交易明细详情
        $status = $order_info['status'];    //订单状态
        $refund_state = $order_info['refund_state'];    //退款状态 0:未退款; 1:部分退款中; 2:部分退款成功; 11:全额退款中; 12:全额退款成功
        if($status == 'WAIT_BUYER_PAY')return return_info(300,'订单未付款');//待付款，不允许操作
        if($status == 'WAIT_CONFIRM')return return_info(300,'订单待确定');//待确定待成团，不允许操作
        if($refund_state == 1 || $refund_state == 11)return return_info(300,'订单退款中');//全额退款中
        if($refund_state == 2 || $refund_state == 12)return return_info(300,'该订单已退款');//全额退款成功
        return return_info(200, '可以预约',$order_detail);
    }
    /**
     * 开始预约，第一步
     */
    public function make_appointment()
    {
        $order_sn = input('post.order_sn');
        if(empty($order_sn))return return_info();

        $post_error = parameter_check(['f_name','f_id_card','f_wx_number','f_pass_check','f_city','f_date','f_time'],1);
        if($post_error['code'] == 300){
            return $post_error;
        }
        $data = $post_error['data'];
        $data['f_order_sn'] = $order_sn;
        $data['f_phone'] = $this->phone;
        $data['f_api'] = 'youzan';//来源
        $data['f_passport'] = input('post.f_passport');//护照（备选填写）
        $data['f_address'] = input('post.f_address');//收货地址
        $data['f_shop'] = input('post.f_shop');//分店

        //检查有赞的平台过来的订单，获取订单中的信息
        $check_order_res = $this->check_order($order_sn);
        if($check_order_res['code'] != 200){
            return $check_order_res;
        }
        $order_detail = $check_order_res['data'];
//        var_dump($order_detail);exit;

        $order_info = $order_detail['full_order_info']['order_info'];   //交易明细详情
        $orders = $order_detail['full_order_info']['orders'][0];    //订单明细结构体
        $data['f_order_time'] = $order_info['created'];    //下单时间

        $num = $orders['num'];//订单中商品数量
        //检查是是否已经预约
        $count = $this->model_form->where([['f_order_sn','=',$order_sn]])->count();//已经约了几次
        if($count >= $num){
            return return_info(300, '该订单名额已约满');
        }
        /**
         * 检查该号码在不在订单订单中。上一个页面已经是查过了，这边就不查了
         */
        //检查该号码在该订单是否已经预约过
        $is_form = $this->model_form->where([['f_order_sn','=',$order_sn],['f_phone','=',$data['f_phone']]])->find();
        if($is_form)return return_info(300, '该订单您已预约');

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
        //根据订单号找订单
        $order = OrderModel::get(['order_sn'=>$order_sn]);
        if($order){
            $data['outer_user_id'] = $order['outer_user_id'];
        }else{
            //订单不存在系统中，表示出现问题，记录错误日志
            $log = new Log();
            $log->log_entry('问题订单号：',$order_sn,'form');//将接收到的原始数据记录日志
        }
        try{
            Db::startTrans();
            $res = $this->model_form->allowField(true)->save($data);
            if(!$res)throw new \Exception('添加表单数据失败');
            $f_id = $this->model_form->f_id;
            if($data['f_type'] == 1){
                //插入hpv记录
                $model_hpv = new HpvModel();
                $hpv = $model_hpv->add_hpv($f_id,$data['f_order_sn'],$data['f_name'],$data['f_phone'],1,$data['f_date'],$data['f_time']);
                if(!$hpv)throw new \Exception('添加hpv数据失败');
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return return_info(300,$e->getMessage());
        }
        return return_info(200,'操作成功',['form_id'=>$f_id]);
    }
    /**
     * 检查预约信息,检查手机号订单号是否已经预约
     */
    public function reservation_info()
    {
        $model_order = new OrderModel();
        $model_hpv = new HpvModel();
        $order_sn = input('get.order_sn');
        $phone = $this->phone;
        if(empty($order_sn))return return_info();
        //检查是否有该订单
        $con = [];
        $con[] = ['buyer_messages|address_info|buyer_words|seller_memo','like', '%'.$phone.'%'];
        $con[] = ['order_sn','=',$order_sn];
        $order = $model_order->getInfo($con);
        if(!$order)return return_info(300,'找不到该订单');

        $where = [];
        $where[] = ['a.f_phone','=', $phone];
        $where[] = ['a.f_order_sn','=', $order_sn];
        $field = 'a.form_id,CONCAT(a.f_name,\'\',(CASE b.f_sex WHEN\'1\' THEN \'男士\' WHEN\'2\' THEN \'女士\' ELSE \'\' END)) name,a.hpv_num,a.plan_state,a.is_see,a.is_see,a.status,CONCAT(a.hpv_date,\' \',a.hpv_time) hpv_date_time,a.finish_time,a.fail_reason,a.create_time,a.hpv_id';
//        $field = 'CASE b.f_sex WHEN\'1\' THEN \'男\' WHEN\'2\' THEN \'女\' ELSE \'未知状态\' END AS f_sex,a.hpv_num,a.plan_state,a.status,CONCAT(a.hpv_date,\' \',a.hpv_time) hpv_date_time,a.finish_time,a.fail_reason,a.create_time';
        $arr = $model_hpv->getInfo($where,[['bo_form b','a.form_id = b.f_id']],$field,'hpv_num desc');
//        echo Db::getLastSql();
        unset($arr['hpv_date'],$arr['hpv_time']);
        if(!$arr){
            $arr = ['hpv_num'=>0];
        }else{
            $model_hpv->where([['hpv_id','=',$arr['hpv_id']]])->update(['is_see'=>$arr['hpv_num']]);
        }
        $title = $order['title'];
        //预约机构
        $arr['f_organization'] = $this->get_organization($title);
        //预约项目
        $arr['f_project'] = $this->get_project($title);

        //处理看过
        return return_info(200,$arr ? '预约信息' : '跳转到第一针预约',$arr);
    }
    /**
     * 根据产品获取预约时间
     */
    public function reservation_time(){
        $model_product_time = new ProductTimeModel();
//        $p_id = input('get.p_id');
        $organization = trim(input('get.organization'));
        $project = trim(input('get.p_name'));
        if(empty($organization) || empty($project))return return_info();

//        $product = ProductModel::get($p_id);
//        if(!$product)return return_info(300, '找不到该产品');
        $where = [];
        $where[] = ['h_name','=',$organization];
        $where[] = ['p_name','=',$project];
        $where[] = ['pt_stock','>',0];
        $time_arr = $model_product_time->getListInfo($where,[],"CONCAT(pt_date,'-',pt_day) date_day,pt_time",'date_day asc, pt_time asc');
//        echo Db::getLastSql();
        if(!$time_arr)return return_info(300,'该产品暂无可预约时间');
        $newArr=[];
        foreach ($time_arr as $k => $val) {    //数据根据日期分组
            $newArr[$val['date_day']][] = $val['pt_time'];
        }

        return return_info(200,'可预约时间',$newArr);
    }
    /**
     * 预约二三针
     */
    public function other_stitches(){
        $model_hpv = new HpvModel();
        try{
            $post_error = parameter_check(['f_id','num','f_date','f_time'],1);
            if($post_error['code'] == 300){
                throw new \Exception($post_error['message']);
            }
            $data = $post_error['data'];
            $form_info = $this->model_form->getInfo([['f_id','=',$data['f_id']]]);
            if(!$form_info)throw new \Exception('找不到该表单');

            //检查是否已经存在记录
            $hpv = $model_hpv->getInfo([['form_id','=',$data['f_id']],['hpv_num','=',$data['num']]]);
            if($hpv){
                //如果是预约失败或者未出席，可以改签
                if($hpv['plan_state'] == 3 || $hpv['status'] == 3){
                    $hpv_data['hpv_date'] = $data['f_date'];
                    $hpv_data['hpv_date'] = $data['f_date'];
                    $hpv_data['plan_state'] = 1;
                    $hpv_data['status'] = 1;
                    $hpv_data['finish_time'] = null;
                    $hpv_data['fail_reason'] = '';
                    $res = $model_hpv->save($hpv_data,[['hpv_id','=',$hpv['hpv_id']]]);
                }else{
                    throw new \Exception('您已预约请等待');
                }
            }else{
                //增加HPV记录
                $res = $model_hpv->add_hpv($data['f_id'], $form_info['f_order_sn'], $form_info['f_name'], $form_info['f_phone'], $data['num'], $data['f_date'], $data['f_time']);
            }
            if(!$res){
                throw new \Exception('HPV记录失败');
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $err_arr = return_info(300,$e->getMessage());
            return $err_arr;
        }
        return return_info(200,'操作成功');
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
     * （废弃）预约日期和时间，第二步
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
                    $res = $model_hpv->add_hpv($data['f_id'], $form_info['f_order_sn'], $form_info['f_name'], $form_info['f_phone'], $data['type'], $data['f_date'], $data['f_time']);
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
    /**
     * 分店列表
     */
    public function store_list(){
        $model_hospital = new HospitalModel();

        $organization = input('get.organization');//机构名称
        if(empty($organization))return return_info();
        //找到对应的机构id
        $h_id = $model_hospital->getInfo([['h_name','=',$organization]],[],'h_id');
        $list = [];
        if(!empty($h_id)){
            $list = $model_hospital->getListInfo([['h_pid','=',$h_id['h_id']]],[],'h_id,h_name');
        }

        return return_info(200,'分店列表',$list);
    }


}