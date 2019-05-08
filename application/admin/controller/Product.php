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
            $product = $this->model_product->allowField(true)->save($data);
            $p_id = $this->model_product->p_id;
        }
        //处理时间
        foreach ($pt_time_arr as $k=>$v){
            if(empty($data['pt_id']))unset($pt_time_arr[$k]['pt_id']);
            $pt_time_arr[$k]['h_id'] = $data['h_id'];
            $pt_time_arr[$k]['h_name'] = $data['h_name'];
            $pt_time_arr[$k]['p_id'] = $p_id;
            $pt_time_arr[$k]['p_name'] = $data['p_name'];
        }
        //添加产品销售日期
        $res = $this->model_product_time->saveAll($pt_time_arr);
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
            $where[] = get_query_time('create_time',$query_start_time,$query_end_time);
        }
        $field = 'p_id,p_name,h_name,create_time';
        $list = $this->model_product->getListPageTotalInfo($where, [], $field, 10);
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
            return return_info(300,'删除失败');
        }
        //日期数据
        $res['h_time_arr'] = $this->model_product_time->getListInfo([['p_id','=',$p_id]],[],'pt_id,pt_date,pt_day,pt_stock');

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
