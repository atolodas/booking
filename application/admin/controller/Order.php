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
        $where = array();
        if(!empty($field_value)){
            $map1 = [
                ['buyer_messages', 'like', '%'.$field_value.'%'],
            ];
            $map2 = [
                ['address_info', 'like', '%'.$field_value.'%'],
            ];
            $where = [ $map1, $map2];
        }
        $field = 'o_id,order_sn,buyer_messages,address_info,created';
        $list = $this->model_order->whereOr($where)->field($field)->order('o_id desc')->paginate(15)->toArray();;
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>&$v){
            $v['buyer_messages'] = unserialize($v['buyer_messages']);
            $v['address_info'] = unserialize($v['address_info']);
        }

        return return_info('200', '订单管理列表', $list);
    }


}
