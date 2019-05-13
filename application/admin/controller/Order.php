<?php
namespace app\admin\controller;

use app\home\model\OrderModel;
use think\Db;

class Order extends AdminController
{
    private $model_order = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_order = new OrderModel();
    }
    /**
     * 订单管理列表
     */
    public function order_manage(){
//        $field_name = input('get.field_name');  //name：姓名，phone:名称
        $field_value = input('get.field_value');    //
        $buyer_words = input('get.buyer_words');    //买家留言
        $seller_memo = input('get.seller_memo');    //卖家留言
        $where = $whereor = array();
        if(!empty($buyer_words)){
            $where[] = ['buyer_words','like', '%'.$buyer_words.'%'];
        }
        if(!empty($seller_memo)){
            $where[] = ['seller_memo','like', '%'.$seller_memo.'%'];
        }
        if(!empty($field_value)){
            $map1 = [
                ['buyer_messages', 'like', '%'.$field_value.'%'],
            ];
            $map2 = [
                ['address_info', 'like', '%'.$field_value.'%'],
            ];
            $whereor = [ $map1, $map2];
        }
        $field = 'o_id,order_sn,buyer_messages,address_info,buyer_words,seller_memo,buyer_words,seller_memo,created';
        $list = $this->model_order->where($where)->whereOr($whereor)->field($field)->order('o_id desc')->paginate(10)->toArray();;
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>&$v){
            $v['buyer_messages'] = unserialize($v['buyer_messages']);
            $v['address_info'] = unserialize($v['address_info']);
        }

        return return_info('200', '订单管理列表', $list);
    }


}
