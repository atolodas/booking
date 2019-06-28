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
        $h_id = input('post.h_id');//
        $h_pid = input('post.h_pid') ? input('post.h_pid') : 0;
        $h_name = input('post.h_name');//
        $h_shop_arr = input('post.h_shop_arr');//分店信息，数组
        if(empty($h_name) || !isset($h_shop_arr)){
            return return_info();
        }

        $where = [['h_name','=',$h_name]];
        $where[] = ['h_pid','=',$h_pid];
        if($h_id){  //修改
            $where[] = ['h_id','neq',$h_id];
        }
        //检查同一机构下该分店是否已经添加过
        $hos = $this->model_hospital->getInfo($where);
        if($hos){
            return return_info(300,'该医院已存在');
        }
        $data = [];
        $data['h_name'] = $h_name;
        $data['h_pid'] = $h_pid;
        if($h_id){  //修改
            $this->model_hospital->where([['h_id','=',$h_id]])->update($data);
        }else{
            $hospital = HospitalModel::create($data);
            $h_id = $hospital->h_id;
        }
        //分店信息
        $h_shop_arr = json_decode($h_shop_arr,true);
        //获取分店主键组合，除此之外的时间全部删除
        $h_id_arr = array_filter(array_column($h_shop_arr,'pt_id'));
        if($h_id_arr){
            $this->model_hospital->where([['h_pid','=',$h_id],['h_id','not in',$h_id_arr]])->delete();
//            echo Db::getLastSql();
        }
        foreach ($h_shop_arr as $k=>$v){
            if(!$v['h_id'])unset($h_shop_arr[$k]['h_id']);
            $h_shop_arr[$k]['h_pid'] = $h_id;
        }
        $res = $this->model_hospital->saveAll($h_shop_arr);
//        echo Db::getLastSql();
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
        $where[] = ['h_pid','=', 0];
        if($h_name){
            $where[] = ['h_name','like', '%'.$h_name.'%'];
        }
        if (!empty($query_start_time) || !empty($query_end_time)) {
            $where[] = getQueryTime('create_time',$query_start_time,$query_end_time);
        }
        $field = 'h_id,h_name,h_pid,create_time';
        $list = $this->model_hospital->getListPageTotalInfo($where, [], $field);
//        echo Db::getLastSql();
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['child'] = $this->model_hospital->getListInfo([['h_pid','=',$v['h_id']]], [], 'h_id,h_name');
//            $h_name_arr = $this->model_hospital->where([['h_pid','=',$v['h_id']]])->column('h_name');
//            $list['data'][$k]['child'] = implode($h_name_arr,',');
        }
        return return_info('200', '医院管理列表', $list);
    }
    /**
     * 医院列表
     */
    public function hospital_list(){
        $list = $this->model_hospital->getHospitalList();
        return $list;
    }
    /**
     * 删除医院，通过外键会删除对应子表中的数据，谨慎操作
     */
    public function hospital_del(){
        $h_id = input('post.h_id');
        if(empty($h_id)){
            return return_info('300');
        }
        $res = $this->model_hospital->where([['h_pid','=',$h_id]])->whereOr([['h_id','=',$h_id]])->delete();
//        $res = $this->model_hospital->where([['h_pid','=',$h_id]])->whereOr([['h_id','=',$h_id]])->update(['delete_time'=>date('Y-m-d H:i:s')]);
        //检查底下是否有分店，有分店将分店信息删除
//
//        $user = HospitalModel::get($h_id);
//        // 软删除
//        $res = $user->delete();
        if($res){
            return return_info(200,'删除成功');
        }else{
            return return_info(300,'删除失败');
        }
    }

}
