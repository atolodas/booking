<?php
namespace app\admin\controller;

use think\Db;
use app\home\model\HospitalModel;

class Hospital extends AdminController
{
    private $model_hospital = [];
    private $model_hospital_time = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_hospital = new HospitalModel();
    }
    /**
     * 添加/修改医院信息
     */
    public function hospital_operation()
    {
        $h_id = input('post.h_id');

        $post_error = parameter_check(['h_name','h_remark'],1);
        if($post_error['code'] == 300){
            return $post_error;
        }
        $data = $post_error['data'];
        $where = [['h_name','=',$data['h_name']]];
        if($h_id){  //修改
            $where[] = ['h_id','neq',$h_id];
        }
        //检查医院是否已经添加过
        $hos = $this->model_hospital->getInfo($where);
        if($hos){
            return return_info(300,'该医院已存在');
        }
        if($h_id){  //修改
            $this->model_hospital->where([['h_id','=',$h_id]])->update($data);
        }else{
            $this->model_hospital->allowField(true)->save($data);
            $h_id = $this->model_hospital->h_id;
        }
        if($h_id){
            return return_info(200,'操作成功');
        }else{
            return return_info(300,'操作失败');
        }
    }
    /**
     * 医院管理列表
     */
    public function hospital_manage(){
        $h_name = input('get.h_name');
        $where = array();
        if($h_name){
            $where[] = ['h_name','like', '%'.$h_name.'%'];
        }
        $field = '';
        $list = $this->model_hospital->getListPageTotalInfo($where, [], $field, 10);
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>$v){
//            $h_time_arr = $this->model_hospital_time->getListInfo([['h_id','=',$v['h_id']]],[],'ht_id,ht_date,ht_day,ht_stock');
//            $list['data'][$k]['h_time_arr'] = $h_time_arr;
        }

        return return_info('200', '医院管理列表', $list);
    }
    /**
     * 医院列表
     */
    public function hospital_list(){
        $list = $this->model_hospital->getListInfo([],[],'h_id,h_name');
        return return_info('200', '医院列表', $list);
    }
    /**
     * 删除医院，通过外键会删除对应子表中的数据，谨慎操作
     */
    public function hospital_del(){
        $h_id = input('post.h_id');
        if(empty($h_id)){
            return return_info('300');
        }
        $res = $this->model_hospital->delInfo([['h_id','=',$h_id]]);
        if($res){
            return return_info(200,'删除成功');
        }else{
            return return_info(300,'删除失败');
        }
    }

}
