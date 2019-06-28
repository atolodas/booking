<?php
namespace app\home\model;
//use think\model\concern\SoftDelete;

class HospitalModel extends BookingModel
{
    protected $pk = 'h_id';
    protected $table = 'bo_hospital';

//    use SoftDelete;
//    protected $deleteTime = 'delete_time';
//    protected $type = [
//        'delete_time'=> 'datetime'
//    ];

    public function getHospitalList(){
        $h_name = input('get.h_name');
        $h_pid = input('get.h_pid') ? input('get.h_pid') : 0;   //-1：全部；0：机构；其他就为获取机构分店
        $where = [];
        if($h_pid != -1){
            $where[] = ['h_pid','=', $h_pid];
        }
        if($h_name){
            $where[] = ['h_name','like', '%'.$h_name.'%'];
        }
        $list = $this->getListInfo($where,[],'h_id,h_name');
        return return_info('200', '医院列表', $list);
    }
}