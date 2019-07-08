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
        $field_value = trim(input('get.field_value'));    //
        $title = trim(input('get.title'));    //产品名称
        $properties_time = trim(input('get.properties_time'));    //预约日期
        $is_outexcel = input('get.is_outexcel');
        $where = array();
        if(!empty($field_value)){
            $where[] = ['buyer_messages|address_info|buyer_words|seller_memo','like', '%'.$field_value.'%'];
        }
        if(!empty($title)){
            $where[] = ['title','like', '%'.$title.'%'];
        }
        if(!empty($properties_time)){
            $where[] = ['properties_time','like', '%'.$properties_time.'%'];
        }
        $field = 'title,order_sn,raw_messages,sku_properties_name,address_info,buyer_words,seller_memo,status_str,created';
        if ($is_outexcel == 1) {  //导出
            $list['data'] = $this->model_order->getListInfo($where, [], $field, 'created desc');
        } else {
            $field .= ',o_id';
            $list = $this->model_order->where($where)->field($field)->order('created desc')->paginate(10)->toArray();
        }
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>$v){
            $v['address_info'] = !empty(unserialize($v['address_info'])) ? implode("\n",unserialize($v['address_info'])) : '';
            $raw_messages = json_decode($v['raw_messages'],true);
            $sku_properties_name = json_decode($v['sku_properties_name'],true);
            $str = '';
            $str1 = '';
            array_filter($raw_messages, function ($value) use ($raw_messages, &$str){
                $str .= array_search($value, $raw_messages) . ':' . $value . "\n";
            });
            array_filter($sku_properties_name, function ($value1) use ($sku_properties_name, &$str1){
                $str1 .= $value1['k'] . ':' . $value1['v'] . "\n";
            });
            $v['raw_messages'] = $str;
            $v['sku_properties_name'] = $str1;
            $list['data'][$k] = $v;
        }
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '订单编号';
            $arr1[] = '产品名称';
            $arr1[] = '下单填写信息';
            $arr1[] = '预约信息';
            $arr1[] = '收货地址信息';
            $arr1[] = '买家留言';
            $arr1[] = '卖家留言';
            $arr1[] = '状态';
            $arr1[] = '下单时间';
            createExcel($arr1, $list['data'], '订单管理列表');
        } else {
            return return_info('200', '订单管理列表', $list);
        }
    }
    public function order_outexcel(){
        echo '修改代码再运行';exit;
        set_time_limit(0);

        $query_start_time = input('get.query_start_time');    //
        $query_end_time = input('get.query_end_time');    //
        $field = 'o_id,sku_properties_name';
        $where = [];
        if (!empty($query_start_time) || !empty($query_end_time)) {//
            $where[] = ['created','between',[$query_start_time,$query_end_time]];
        }
        $list['data'] = $this->model_order->getListInfo($where, [], $field,'created desc');
//        $list = $this->model_order->where($where)->field($field)->order('created desc')->paginate(100)->toArray();
//        echo Db::getLastSql();
        $arr = [];
        foreach ($list['data'] as $k=>$v){
            $sku_properties_name = json_decode($v['sku_properties_name'],true);
            $year_mouth =$day_info= $store='';
            $str1 = [];
            if($sku_properties_name){
                foreach ($sku_properties_name as $key2=>$value2){
//                $str1 = array_merge($str1,[$value2['k']=>$value2['v']]);
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
//                    case '预约门店':
//                        $store = $value2['v'];
//                        break;
                    }
                }

            }
            $data = [];
            if($year_mouth.$day_info){
                $data['o_id'] = $v['o_id'];
//                $data['sku_properties_name'] = $sku_properties_name;
                $data['properties_time'] = $year_mouth.$day_info;
                $arr[] = $data;
            }

//            $data['store'] = $store;
//            $v['sku_properties_name'] = $str1;
//            $list['data'][$k] = $data;
        }
//        return $arr;
        $this->model_order->saveAll($arr);
    }


}
