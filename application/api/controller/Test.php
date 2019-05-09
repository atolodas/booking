<?php
/**
 * 用于测试
 */
namespace app\api\controller;

use app\home\model\OrderModel;
use app\lib\open\Youzan;
use think\Controller;

class Test extends Controller
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
        $con['start_created'] = '2019-01-01 00:00:00';
        $con['end_created'] = '2019-05-08 11:01:55';
        $order_list = $this->youzan->youzan_order_list($con);
        if($order_list['code'] !== 200)return return_info(300, '出错');

        $order_list = $order_list['data']['full_order_info_list'];
        $arr = [];
        foreach ($order_list as $k=>$v){
            $arr[$k]['order_sn'] = $v['full_order_info']['order_info']['tid'];
            $arr[$k]['title'] = $v['full_order_info']['orders'][0]['title'];
            $arr[$k]['created'] = $v['full_order_info']['order_info']['created'];
            $arr[$k]['pay_time'] = $v['full_order_info']['order_info']['pay_time'];
            $address_info = $v['full_order_info']['address_info'];
            $address_info = ['receiver_name'=>$address_info['receiver_name'],'receiver_tel'=>$address_info['receiver_tel']];
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
        }
//        return $arr;
        if(count($arr) < 1 || $page_no == 100){ //页码，从1开始，最大不能超过100（有赞文档上的限制）
            return $page_no;
        }else{
            $this->model_order->saveAll($arr);
            return $this->get_order($list,$page_no+1);
        }
    }
}