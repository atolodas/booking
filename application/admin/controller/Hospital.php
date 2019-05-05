<?php
namespace app\admin\controller;

use think\Db;
use app\home\model\HospitalModel;

class Hospital extends AdminController
{
    private $model_hospital = [];
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
        $query_start_time = input('get.query_start_time');    //开始时间xxxx-xx-xx
        $query_end_time = input('get.query_end_time');    //结束时间xxxx-xx-xx
        $where = array();
        if($h_name){
            $where[] = ['h_name','like', '%'.$h_name.'%'];
        }
        if (!empty($query_start_time) || !empty($query_end_time)) {
            $where[] = get_query_time('create_time',$query_start_time,$query_end_time);
        }
        $field = '';
        $list = $this->model_hospital->getListPageTotalInfo($where, [], $field, 10);
//        echo Db::getLastSql();

        return return_info('200', '医院管理列表', $list);
    }
    /**
     * 医院列表
     */
    public function hospital_list(){
        $h_name = input('get.h_name');
        $where = [];
        if($h_name){
            $where[] = ['h_name','like', '%'.$h_name.'%'];
        }
        $list = $this->model_hospital->getListInfo($where,[],'h_id,h_name');
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
