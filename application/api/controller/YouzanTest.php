<?php
/**
 * 用于测试
 */
namespace app\api\controller;

use app\home\model\OrderModel;
use app\lib\open\Youzan;
use think\Controller;
use think\Db;

class YouzanTest extends Controller
{
    private $model_order = [];
    private $youzan = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_order = new OrderModel();
        $this->youzan = new Youzan();
    }
    /**
     * 将有赞历史订单的数据插入库
     */
    public function get_order($list=array(),$page_no = 1){
        echo '修改代码再运行';exit;

        set_time_limit(0);
        $con = [];
        $con['page_no'] = $page_no;
        $con['page_size'] = 100;
        $con['start_created'] = '2019-05-28 14:16:43';
        $con['end_created'] = date('Y-m-d H:i:s',time());
        $order_list = $this->youzan->youzan_order_list($con);
//        var_dump($order_list);exit;
//        return $order_list;exit;
        if($order_list['code'] !== 200)return return_info(300, '出错');

        $order_list = $order_list['data']['full_order_info_list'];
        $arr = [];
        foreach ($order_list as $k=>$v){
            $arr[$k]['order_sn'] = $v['full_order_info']['order_info']['tid'];
            $arr[$k]['title'] = $v['full_order_info']['orders'][0]['title'];
            $arr[$k]['total_fee'] = $v['full_order_info']['orders'][0]['total_fee'];
            $arr[$k]['num'] = $v['full_order_info']['orders'][0]['num'];
            $arr[$k]['status'] = !empty($v['full_order_info']['order_info']['status']) ? $v['full_order_info']['order_info']['status'] : '';
            $arr[$k]['status_str'] = !empty($v['full_order_info']['order_info']['status_str']) ? $v['full_order_info']['order_info']['status_str'] : '';
            $arr[$k]['created'] = $v['full_order_info']['order_info']['created'];
            $arr[$k]['pay_time'] = $v['full_order_info']['order_info']['pay_time'];
            if(!empty($v['full_order_info']['address_info'])){
                $address_info = $v['full_order_info']['address_info'];
                $address_info = ['receiver_name'=>$address_info['receiver_name'],'receiver_tel'=>$address_info['receiver_tel']];
            }else{
                $address_info = ['receiver_name'=>'','receiver_tel'=>''];
            }
            $arr[$k]['address_info'] = serialize($address_info);  //收货地址信息
            $buyer_messages = $v['full_order_info']['orders'][0]['buyer_messages'];
            if(!empty($buyer_messages)){
                $buyer_note = [];
                foreach (json_decode($buyer_messages,true) as $key => $vo) {
                    switch (trim($key)) {
                        case '姓名':
                            $buyer_note['realname']=$vo;
                            break;
                        case '手机号码':
                        case '手机':
                            $buyer_note['mobile']=$vo;
                            break;
                    }
                }
                $arr[$k]['buyer_messages'] = serialize($buyer_note);  //
            }
            if(!empty($v['full_order_info']['remark_info'])){
                $arr[$k]['buyer_words'] = !empty($v['full_order_info']['remark_info']['buyer_message']) ? $v['full_order_info']['remark_info']['buyer_message'] : '';//买家留言
                $arr[$k]['seller_memo'] = !empty($v['full_order_info']['remark_info']['trade_memo']) ? $v['full_order_info']['remark_info']['trade_memo'] : '';//卖家留言
            }
        }
//        return $arr;
        if(count($arr) < 1 || $page_no == 100){ //页码，从1开始，最大不能超过100（有赞文档上的限制）
            return $page_no;
        }else{
            $this->model_order->saveAll($arr);
            return $this->get_order($list,$page_no+1);
        }
    }

    //更新订单数据
    public function update_order($page_no = 1){
//        echo '修改代码再运行';exit;

//        $res = $this->model_order->getListInfo([['o_id','=','24040']],[],'o_id,raw_messages,outer_user_arr');
//        foreach ($res as $k=>$v){
//            $res[$k]['raw_messages'] = json_decode($v['raw_messages'],true);
//            $res[$k]['outer_user_arr'] = json_decode($v['outer_user_arr'],true);
//        }
//        return $res;
//        exit;

        set_time_limit(0);
        $con = [];
        $con['page_no'] = $page_no;
        $page_size = 20;
        $con['page_size'] = $page_size;
        $con['start_created'] = '2019-04-25 00:00:00';
        $con['end_created'] = '2019-04-26 09:15:55';
//        $con['start_created'] = '2017-11-15 00:00:00';
//        $con['end_created'] = '2017-11-19 14:58:50';
//        $con['end_created'] = date('Y-m-d H:i:s');
        $order_list = $this->youzan->youzan_order_list($con);
//        var_dump($order_list);exit;
//        return $order_list;exit;
        if($order_list['code'] !== 200){
            var_dump($order_list);
            return return_info(300, '出错');
        }

        $order_list = $order_list['data']['full_order_info_list'];
        $arr = [];
        foreach ($order_list as $k=>$v){
            $order_sn = $v['full_order_info']['order_info']['tid'];
            $o_id = $this->model_order->getInfo([['order_sn','=',$order_sn]],[],'o_id');
            if($o_id){
                $data = [];
//                $data['o_id'] = $o_id['o_id'];
                $buyer_messages = $v['full_order_info']['orders'][0]['buyer_messages'];
                if(!empty($buyer_messages)){
                    $data['raw_messages'] = json_encode(json_decode($buyer_messages,true));  //
                }
                $data['sku_properties_name'] = isset($v['full_order_info']['orders'][0]['sku_properties_name']) ? $v['full_order_info']['orders'][0]['sku_properties_name'] : '';
                $fans_id = $v['full_order_info']['buyer_info']['fans_id'];
                if($fans_id){

                    $user_weixin = $this->youzan->youzan_user_weixin(['fans_id'=>$fans_id]);
                    $user_arr = [];
                    if($user_weixin['code'] == 200){
                        $user_arr['fans_id'] = $fans_id;
                        $user_arr['weixin_openid'] = $user_weixin['data']['user']['weixin_openid'];
                        $user_arr['union_id'] = $user_weixin['data']['user']['union_id'];
//                    $user_arr['fans_nickname'] = $user_weixin['data']['user']['fans_nickname'];
                        $data['outer_user_id'] = $user_arr['weixin_openid'];
                        $data['outer_user_arr'] = json_encode($user_arr);
                    }
                }
//                $data['status'] = !empty($v['full_order_info']['order_info']['status']) ? $v['full_order_info']['order_info']['status'] : '';
//                $data['status_str'] = !empty($v['full_order_info']['order_info']['status_str']) ? $v['full_order_info']['order_info']['status_str'] : '';
//                $data['total_fee'] = $v['full_order_info']['orders'][0]['total_fee']; //订单金额
//                $data['num'] = $v['full_order_info']['orders'][0]['num']; //订单数量
//                $data['pay_time'] = $v['full_order_info']['order_info']['pay_time'];  //支付时间
//                $data['refund_state'] = $v['full_order_info']['order_info']['refund_state'];//没能同步退款状态则暂时不导退款状态数据
//                $data['buyer_words'] = !empty($v['full_order_info']['remark_info']['buyer_message']) ? $v['full_order_info']['remark_info']['buyer_message'] : '';//买家留言
//                $data['seller_memo'] = !empty($v['full_order_info']['remark_info']['trade_memo']) ? $v['full_order_info']['remark_info']['trade_memo'] : '';//卖家留言
//                var_dump($data);//8054出错
                var_dump($o_id['o_id']);
                $arr = $this->model_order->where([['o_id','=',$o_id['o_id']]])->update($data);
//                echo Db::getLastSql();
//                $arr[] = $data;
            }
        }
//        exit;
//        return $arr;
        if(count($arr) < 1 || $page_no == 100){ //页码，从1开始，最大不能超过100（有赞文档上的限制）
            return $page_no;
        }else{
//            $this->model_order->saveAll($arr);
//            sleep(1);
            return $this->update_order($page_no+1);
        }
    }
    //更新订单数据
    public function update_order1($page_no = 1){
//        echo '修改代码再运行';exit;

        set_time_limit(0);
        $con = [];
        $con['page_no'] = $page_no;
        $con['page_size'] = 20;
//        $con['start_created'] = '2018-11-16 10:33:14';
//        $con['end_created'] = '2019-06-28 00:25:09';
        $con['start_created'] = '2019-06-10 00:00:00';
        $con['end_created'] = '2019-06-14 23:59:59';
//        $con['end_created'] = date('Y-m-d H:i:s');
        $order_list = $this->youzan->youzan_order_list($con);
//        var_dump($order_list);exit;
//        return $order_list;exit;
        if($order_list['code'] !== 200){
            var_dump($order_list);
            return return_info(300, '出错');
        }

        $order_list = $order_list['data']['full_order_info_list'];
        $arr = [];
        foreach ($order_list as $k=>$v){
            $order_sn = $v['full_order_info']['order_info']['tid'];
            $o_id = $this->model_order->getInfo([['order_sn','=',$order_sn]],[],'o_id');
            if($o_id){
                $data = [];
//                $data['o_id'] = $o_id['o_id'];
                $buyer_messages = $v['full_order_info']['orders'][0]['buyer_messages'];
                if(!empty($buyer_messages)){
                    $data['raw_messages'] = json_encode(json_decode($buyer_messages,true));  //
                }
                $data['sku_properties_name'] = isset($v['full_order_info']['orders'][0]['sku_properties_name']) ? $v['full_order_info']['orders'][0]['sku_properties_name'] : '';
                $fans_id = $v['full_order_info']['buyer_info']['fans_id'];
                if($fans_id){

                    $user_weixin = $this->youzan->youzan_user_weixin(['fans_id'=>$fans_id]);
                    $user_arr = [];
                    if($user_weixin['code'] == 200){
                        $user_arr['fans_id'] = $fans_id;
                        $user_arr['weixin_openid'] = $user_weixin['data']['user']['weixin_openid'];
                        $user_arr['union_id'] = $user_weixin['data']['user']['union_id'];
//                    $user_arr['fans_nickname'] = $user_weixin['data']['user']['fans_nickname'];
                        $data['outer_user_id'] = $user_arr['weixin_openid'];
                        $data['outer_user_arr'] = json_encode($user_arr);
                    }
                }
//                $data['status'] = !empty($v['full_order_info']['order_info']['status']) ? $v['full_order_info']['order_info']['status'] : '';
//                $data['status_str'] = !empty($v['full_order_info']['order_info']['status_str']) ? $v['full_order_info']['order_info']['status_str'] : '';
//                $data['total_fee'] = $v['full_order_info']['orders'][0]['total_fee']; //订单金额
//                $data['num'] = $v['full_order_info']['orders'][0]['num']; //订单数量
//                $data['pay_time'] = $v['full_order_info']['order_info']['pay_time'];  //支付时间
//                $data['refund_state'] = $v['full_order_info']['order_info']['refund_state'];//没能同步退款状态则暂时不导退款状态数据
//                $data['buyer_words'] = !empty($v['full_order_info']['remark_info']['buyer_message']) ? $v['full_order_info']['remark_info']['buyer_message'] : '';//买家留言
//                $data['seller_memo'] = !empty($v['full_order_info']['remark_info']['trade_memo']) ? $v['full_order_info']['remark_info']['trade_memo'] : '';//卖家留言

//                $arr[] = $data;
                var_dump($o_id['o_id']);
                $arr = $this->model_order->where([['o_id','=',$o_id['o_id']]])->update($data);
//                echo Db::getLastSql();
            }
        }
//        return $arr;
        if(count($arr) < 1 || $page_no == 100){ //页码，从1开始，最大不能超过100（有赞文档上的限制）
            return $page_no;
        }else{
//            $this->model_order->saveAll($arr);
//            sleep(1);
            return $this->update_order1($page_no+1);
        }
    }
}