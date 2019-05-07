<?php
namespace app\api\controller;

use app\home\model\OrderModel;
/**
 * 有赞推送服务消息接收
 */
class YouzanPush{

    /**
     * 获取有赞推送信息
     */
    public function get_push_info(){
        echo 111;exit;
        $yz_log = new \app\lib\Log();

        require_once __DIR__ . '/../../lib/open/YzConfig.php';
        $config = new \YzConfig();
        $client_id = $config->app_id;
        $client_secret = $config->app_secret;

        $json = file_get_contents('php://input');
        $json_data = json_decode($json, true);
//        $yz_log->log_entry('有赞原始数据',$json);//将接收到的原始数据记录日志
        //判断消息是否合法，若合法则返回成功标识
        $msg = $json_data['msg'];
        $sign_string = $client_id."".$msg."".$client_secret;
        $sign = md5($sign_string);
//        if($sign != $data['sign']){
//            exit();
//        }else{
//            $result = array("code"=>0,"msg"=>"success") ;
//            var_dump($result);
//        }

        $msg = json_decode('{
  "full_order_info": {
    "orders": [
      {
        "title": "宜家水杯",
        "num": 100,
        "price": "100.00",
        "payment": "90.00",
        "item_type": 0,
        "outer_item_id": "ABC",
        "outer_sku_id": "13323",
        "pic_path": "https://img.com",
        "sku_properties_name": "白色",
        "buyer_messages": "{\"姓名\":\"熊茉莉\",\"手机\":\"18051096336\",\"出生日期\":\"2016-06-06\",\"打针时间\":\"01:00\",\"所在城市\":\"苏州\",\"身份证号\":\"360926201606060020\"}",
        "alias": "3f40ohzie8yfa",
        "points_price": "0",
        "oid": 123456,
        "sku_id": 123,
        "is_present": "false",
        "total_fee": "90.00",
        "item_id": 123456,
        "goods_url": "https://h5.youzan.com/v2/goods/3f40ohzie8yfa",
        "discount_price": "0.01"
      }
    ],
    "order_info": {
      "status": "WAIT_BUYER_PAY",
      "type": 0,
      "tid": "E20180101111112340001",
      "status_str": "已付款",
      "pay_type": 0,
      "close_type": 0,
      "expired_time": "2018-01-01 00:00:00",
      "express_type": 0,
      "refund_state": 0,
      "team_type": 0,
      "consign_time": "2018-01-01 00:00:00",
      "update_time": "2018-01-01 00:00:00",
      "offline_id": 0,
      "created": "2018-01-01 00:00:00",
      "pay_time": "2018-01-01 00:00:00",
      "confirm_time": "2018-01-01 00:00:00",
      "is_retail_order": false,
      "success_time": "2018-01-01 00:00:00",
      "order_extra": {
        "is_from_cart": "false",
        "cashier_id": "123",
        "cashier_name": "收银员",
        "invoice_title": "抬头",
        "settle_time": "1525844042082",
        "is_parent_order": "false",
        "is_sub_order": "false",
        "fx_order_no": "E2018",
        "fx_kdt_id": "123",
        "parent_order_no": "E2018",
        "purchase_order_no": "E2018",
        "dept_id": "123",
        "create_device_id": "123",
        "is_points_order": "false",
        "id_card_number": "123"
      },
      "order_tags": {
        "is_virtual": false,
        "is_purchase_order": false,
        "is_fenxiao_order": false,
        "is_member": false,
        "is_preorder": false,
        "is_offline_order": false,
        "is_multi_store": false,
        "is_settle": null,
        "is_payed": false,
        "is_secured_transactions": false,
        "is_postage_free": false,
        "is_feedback": false,
        "is_refund": false
      }
    },
    "remark_info": {
      "star": 0,
      "trade_memo": "尽快支付",
      "buyer_message": "尽快发货"
    },
    "address_info": {
      "receiver_name": "刘德华",
      "delivery_address": "学院路8888号",
      "address_extra": "{ln:23.43232,lat:9879.3443243}",
      "delivery_district": "西湖区",
      "delivery_end_time": "2018-01-01 00:00:00",
      "delivery_postal_code": "000000",
      "self_fetch_info": "{}",
      "delivery_province": "浙江",
      "delivery_start_time": "2018-01-01 00:00:00",
      "receiver_tel": "15899898989",
      "delivery_city": "杭州市"
    },
    "buyer_info": {
      "buyer_id": 63889,
      "fans_id": 3233,
      "fans_type": 1,
      "fans_nickname": "ketty",
      "buyer_phone": "15898999998",
      "outer_user_id": "123"
    },
    "source_info": {
      "source": {
        "platform": "wx",
        "wx_entrance": "wx_gzh"
      },
      "is_offline_order": false
    },
    "pay_info": {
      "payment": "110.99",
      "post_fee": "10.00",
      "outer_transactions": [
        "[]"
      ],
      "total_fee": "100.99",
      "transaction": [
        "[]"
      ]
    },
    "child_info": {
      "gift_no": "送礼编号",
      "gift_sign": "送礼标记"
    }
  },
  "delivery_order": [
    {
      "pk_id": 1,
      "express_state": 1,
      "express_type": 1,
      "oids": [
        {
          "oid": "123"
        }
      ]
    }
  ],
  "order_promotion": {
    "item": {
      "promotions": {
        "promotion_title": "满减送活动",
        "promotion_type_name": "满减送",
        "promotion_type_id": 1,
        "decrease": "10.00",
        "promotion_type": "0"
      },
      "item_id": 123,
      "is_present": false,
      "sku_id": 123456,
      "oid": 123456
    },
    "order": {
      "promotion_type": "0",
      "promotion_title": "满减送活动",
      "promotion_type_name": "满减送",
      "promotion_type_id": 123,
      "promotion_condition": "满减送XX",
      "promotion_id": 32382137123,
      "sub_promotion_type": "card",
      "discount_fee": "10.00",
      "promotionContent": "满减送XX",
      "coupon_id": "3321hu2tv3123"
    },
    "adjust_fee": "1.00",
    "item_discount_fee": "10.00",
    "order_discount_fee": "1.00"
  },
  "refund_order": [
    {
      "refund_type": 123,
      "refund_fee": "0",
      "refund_id": "0",
      "refund_state": 2,
      "oids": [
        "12"
      ]
    }
  ]
}',true);

        //msg内容经过 urlencode 编码，需进行解码
//        $msg = json_decode(urldecode($msg),true);
        $order_detail = $msg['full_order_info'];
//        var_dump($msg);exit();
//        $yz_log->log_entry('msg数据',$msg);    //将msg数据记录日志
        //根据 type 来识别消息事件类型
//        if($json_data['type'] == "trade_TradeBuyerPay"){
        //买家付款完成创建消息,主订单状态为「等待商家发货」时触发
        $data = array();
        $data['order_sn'] = $order_detail['order_info']['tid'];//订单号
//            $created = $order_detail['order_info']['created'];//下单时间
        $orders = $order_detail['orders'][0];

        $data['title'] = $orders['title'];  //产品名称
        $buyer_note = [];
        foreach (json_decode($orders['buyer_messages'],true) as $k => $v) {
            switch (trim($k)) {
                case '姓名':
                    $buyer_note['realname']=$v;
                    break;
                case '手机号码':
                case '手机':
                    $buyer_note['mobile']=$v;
                    break;
            }
        }
        $data['buyer_messages'] = serialize($buyer_note);  //
        $address_info = $order_detail['address_info'];
        $address_info = ['receiver_name'=>$address_info['receiver_name'],'receiver_tel'=>$address_info['receiver_tel']];
        $data['address_info'] = serialize($address_info);  //收货地址信息
        $model_order = new OrderModel();
        $model_order->save($data);
    }

}

