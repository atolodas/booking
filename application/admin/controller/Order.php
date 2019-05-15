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
        $field_value = input('get.field_value');    //
        $where = array();
        if(!empty($field_value)){
            $where[] = ['buyer_messages|address_info|buyer_words|seller_memo','like', '%'.$field_value.'%'];
        }
        $field = 'o_id,title,order_sn,buyer_messages,address_info,buyer_words,seller_memo,status_str,created';
        $list = $this->model_order->where($where)->field($field)->order('created desc')->paginate(10)->toArray();;
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>&$v){
            $v['buyer_messages'] = unserialize($v['buyer_messages']);
            $v['address_info'] = unserialize($v['address_info']);
        }

        return return_info('200', '订单管理列表', $list);
    }


}
