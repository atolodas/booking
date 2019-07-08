<?php
namespace app\admin\controller;

use app\home\model\ProductModel;
use app\home\model\ProductTimeModel;
use think\Db;

class Product extends AdminController
{
    private $model_product_time = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_product = new ProductModel();
        $this->model_product_time = new ProductTimeModel();
    }
    /**
     * 添加/修改产品销售日期和库存
     */
    public function product_time_operation()
    {
        $p_id = input('post.p_id');

        $post_error = parameter_check(['h_id','h_name','p_name','pt_time_arr'],1);
        if($post_error['code'] == 300){
            return $post_error;
        }
        $data = $post_error['data'];
        $where = [['p_name','=',$data['p_name']]];
        if($p_id){  //修改
            $where[] = ['p_id','neq',$p_id];
        }
        //添加产品
        if($this->model_product->getInfo($where)){
            return return_info(300,'该产品已存在');
        }
        $pt_time_arr = json_decode($data['pt_time_arr'],true);
        if($p_id){  //修改
            unset($data['pt_time_arr']);
            $product = $this->model_product->where([['p_id','=',$p_id]])->update($data);
        }else{
//            $product = $this->model_product->allowField(true)->insertGetId($data);//这种不会调用自动时间戳
            $product = ProductModel::create($data);
            $p_id = $product->p_id;
        }
        $arr = $pt_id_arr = [];
        foreach ($pt_time_arr as $k=>$v){
            foreach ($v['time_stock'] as $k1=>$v1){
                if(!$v1['pt_id']){
                    unset($v1['pt_id']);
                }else{
                    $pt_id_arr[] = $v1['pt_id'];
                }
                $v1['pt_date'] = $v['pt_date'];
                $v1['pt_day'] = $v['pt_day'];
                $v1['h_id'] = $data['h_id'];
                $v1['h_name'] = $data['h_name'];
                $v1['p_id'] = $p_id;
                $v1['p_name'] = $data['p_name'];
                $arr[] = $v1;
            }
        }
//        var_dump($arr);exit;
        //获取时间主键组合，除此之外的时间全部删除
        if($pt_id_arr){
            $this->model_product_time->where([['p_id','=',$p_id],['pt_id','not in',$pt_id_arr]])->delete();
//            echo Db::getLastSql();
        }
        //添加产品销售日期
        $res = $this->model_product_time->saveAll($arr);
//        echo Db::getLastSql();
        if($product || $res){
            return return_info(200,'操作成功');
        }else{
            return return_info(300,'操作失败');
        }
    }
    /**
     * 产品管理列表
     */
    public function product_manage(){
        $p_name = input('get.p_name');
        $h_name = input('get.h_name');
        $query_start_time = input('get.query_start_time');    //开始时间xxxx-xx-xx
        $query_end_time = input('get.query_end_time');    //结束时间xxxx-xx-xx
        $where = array();
        if($p_name)$where[] = ['p_name','like', '%'.$p_name.'%'];
        if($h_name)$where[] = ['h_name','like', '%'.$h_name.'%'];
        if (!empty($query_start_time) || !empty($query_end_time)) {
            $where[] = getQueryTime('create_time',$query_start_time,$query_end_time);
        }
        $field = 'p_id,p_name,h_name,create_time';
        $list = $this->model_product->getListPageTotalInfo($where, [], $field);
//        echo Db::getLastSql();
//        foreach ($list['data'] as $k=>$v){
//            $h_time_arr = $this->model_product_time->getListInfo([['p_id','=',$v['p_id']]],[],'pt_id,pt_date,pt_day,pt_stock');
//            $list['data'][$k]['p_time_arr'] = $h_time_arr;
//        }

        return return_info('200', '产品管理列表', $list);
    }
    /**
     * 产品信息
     */
    public function product_info(){
        $p_id = input('get.p_id');
        if(empty($p_id)){
            return return_info('300');
        }
        $res = $this->model_product->getInfo([['p_id','=',$p_id]],[],'p_id,p_name,h_id,h_name');
        if(!$res){
            return return_info(300,'找不到该商品');
        }
        //日期数据
        $h_time_arr = $this->model_product_time->getListInfo([['p_id','=',$p_id]],[],'pt_id,pt_date,pt_day,CONCAT(pt_date,\'-\',pt_day) date_day,pt_time,pt_stock','date_day asc,pt_time asc');
        $arr =  $arr1 = [];
        foreach($h_time_arr as $k=>$v){
            $arr[$v['date_day']]['pt_date'] = $v['pt_date'];
            $arr[$v['date_day']]['pt_day'] = $v['pt_day'];
            $arr[$v['date_day']]['time_stock'][] = ['pt_id'=>$v['pt_id'],'pt_time'=>$v['pt_time'],'pt_stock'=>$v['pt_stock']];
        }
        $res['h_time_arr'] = array_values($arr);
        return return_info(200,'成功',$res);
    }
    /**
     * 删除产品
     */
    public function product_del(){
        $p_id = input('post.p_id');
        if(empty($p_id)){
            return return_info('300');
        }
        $res = $this->model_product->delInfo([['p_id','=',$p_id]]);
        if($res){
            return return_info(200,'删除成功');
        }else{
            return return_info(300,'删除失败');
        }
    }

}
