<?php
namespace app\api\controller;
use app\lib\open\Youzan;
use think\Db;
use app\home\model\OrderModel;
/**
 * 有赞推送服务消息接收
 */
class YouzanPush{

    /**
     * 获取有赞推送信息
     */
    public function get_push_info(){
        $yz_log = new \app\lib\Log();

        require_once __DIR__ . '/../../lib/open/YzConfig.php';
        $config = new \YzConfig();
        $client_id = $config->app_id;
        $client_secret = $config->app_secret;

        $json = file_get_contents('php://input');
        $json_data = json_decode($json, true);
        $yz_log->log_entry('有赞原始数据',$json);//将接收到的原始数据记录日志
        //判断消息是否合法，若合法则返回成功标识
        $msg = $json_data['msg'];
        $sign_string = $client_id."".$msg."".$client_secret;
        $sign = md5($sign_string);
        if($sign != $json_data['sign']){
            exit();
        }else{
            $result = array("code"=>0,"msg"=>"success") ;
            var_dump($result);
        }
        //msg内容经过 urlencode 编码，需进行解码
        $msg = json_decode(urldecode($msg),true);
        $yz_log->log_entry($json_data['type'].' msg数据',$msg);    //将msg数据记录日志

        $model_order = new OrderModel();
        try
        {
            $data = array();
            //根据 type 来识别消息事件类型
            switch ($json_data['type']){
                case 'trade_TradeCreate':   //交易创建
                    $order_detail = $msg['full_order_info'];
                    //当一笔订单创建时会通知该消息
                    $data['order_sn'] = $order_detail['order_info']['tid'];//订单号
                    $data['status'] = $order_detail['order_info']['status'];//
                    $data['status_str'] = $order_detail['order_info']['status_str'];
                    $data['created'] = $order_detail['order_info']['created'];//下单时间
                    $orders = $order_detail['orders'][0];

                    $data['title'] = $orders['title'];  //产品名称
                    $data['num'] = $orders['num'];  //产品数量
                    $data['total_fee'] = $orders['total_fee'];  //
                    $buyer_note = [];
                    if(!empty($orders['buyer_messages'])){
                        $buyer_messages = json_decode($orders['buyer_messages'],true);
                        $data['raw_messages'] = json_encode($buyer_messages);

                        foreach (json_decode($orders['buyer_messages'],true) as $k => $v) {
                            switch (trim($k)) {
                                case '姓名':
                                    $buyer_note['realname']=$v;
                                    break;
                                case '手机号码':
                                case '手机':
                                case '手机号':
                                    $buyer_note['mobile']=$v;
                                    break;
                            }
                        }
                    }else{
                        $buyer_note['realname']='';
                        $buyer_note['mobile']='';
                    }
                    $data['buyer_messages'] = serialize($buyer_note);  //

                    $sku_properties_name = isset($orders['sku_properties_name']) ? $orders['sku_properties_name'] : '';
                    if($sku_properties_name){
                        $year_mouth =$day_info= '';
                        foreach (json_decode($sku_properties_name,true) as $key2=>$value2){
                            switch (trim($value2['k'])) {
                                case '预约时间':
                                case '月份':
                                case '年份':
                                    $year_mouth = $value2['v'];
                                    break;
                                case '日期':
                                case '日':
                                    $day_info = $value2['v'];
                                    break;
                            }
                        }
                        if($year_mouth.$day_info){
                            $data['properties_time'] = $year_mouth.$day_info;
                        }
                    }
                    $data['sku_properties_name'] = $sku_properties_name;
                    $fans_id = $order_detail['buyer_info']['fans_id'];
                    if($fans_id){
                        $youzan = new Youzan();
                        $user_weixin = $youzan->youzan_user_weixin(['fans_id'=>$fans_id]);
                        $user_arr = [];
                        if($user_weixin['code'] == 200){
                            $user_arr['fans_id'] = $fans_id;
                            $user_arr['weixin_openid'] = $user_weixin['data']['user']['weixin_openid'];
                            $user_arr['union_id'] = $user_weixin['data']['user']['union_id'];
                            $data['outer_user_id'] = $user_arr['weixin_openid'];
                            $data['outer_user_arr'] = json_encode($user_arr);
                        }
                    }
                    $data['buyer_words'] = $order_detail['remark_info']['buyer_message'];  //买家留言
                    $address_info = $order_detail['address_info'];
                    $address_info = ['receiver_name'=>$address_info['receiver_name'],'receiver_tel'=>$address_info['receiver_tel']];
                    $data['address_info'] = serialize($address_info);  //收货地址信息
                    $res = $model_order->save($data);
                    $yz_log->log_entry('订单创建结果',$res);
                    break;
                case 'trade_TradePaid':   //交易支付
                case 'trade_TradeBuyerPay':   //买家付款
                    $order_detail = $msg['full_order_info'];
                    $order_sn = $order_detail['order_info']['tid'];//订单号
                    $data['status'] = $order_detail['order_info']['status'];//
                    $data['status_str'] = $order_detail['order_info']['status_str'];
                    $data['pay_time'] = $order_detail['order_info']['pay_time'];//支付时间
                    $res = $model_order->save($data,[['order_sn','=',$order_sn]]);
                    $mess = '交易支付/买家付款结果';
                    $yz_log->log_entry($mess,$res);
                    break;
                case 'trade_TradePartlySellerShip':   //卖家部分发货
                case 'trade_TradeSellerShip':   //卖家发货
                    $order_sn = $msg['tid'];//订单号
                    $data['status'] = 'WAIT_BUYER_CONFIRM_GOODS';
                    $data['status_str'] = '已发货';
                    $res = $model_order->save($data,[['order_sn','=',$order_sn]]);
                    $mess = '卖家发货结果';
                    $yz_log->log_entry($mess,$res);
                    break;
                case 'trade_TradeMemoModified':   //卖家修改交易备注
                    $seller_memo = $msg['seller_memo'];
                    $tid = $msg['tid'];
                    $res = $model_order->save(['seller_memo'=>$seller_memo],[['order_sn','=',$tid]]);
                    $yz_log->log_entry('卖家修改备注创建结果',$res);
                    break;
                case 'trade_TradeSuccess':   //交易成功
                    $order_sn = $msg['tid'];//订单号
                    $data['status'] = 'TRADE_SUCCESS';
                    $data['status_str'] = '已完成';
                    $res = $model_order->save($data,[['order_sn','=',$order_sn]]);
                    $yz_log->log_entry('交易成功结果',$res);
                    break;
                case 'trade_TradeClose':   //交易关闭
                    $order_sn = $msg['tid'];//订单号
                    $data['status'] = 'TRADE_CLOSED';
                    $data['status_str'] = '已关闭';
                    $res = $model_order->save($data,[['order_sn','=',$order_sn]]);
                    $yz_log->log_entry('订单关闭结果',$res);
                    break;

            }
        }
        catch(\Exception $e)
        {
            $yz_log->log_entry('执行报错',$e);
        }

    }

}

