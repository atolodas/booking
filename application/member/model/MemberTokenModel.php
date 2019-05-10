<?php
namespace app\member\model;

use app\home\model\BookingModel;

class MemberTokenModel extends BookingModel
{
    protected $pk = 'mt_id';
    protected $table = 'bo_member_token';

    /**
     * 存储token
     * @param $member_id
     * @param $member_name  账号
     * @param $password
     * @param $s_type   1：管理后台
     * @return string
     */
    public function save_token($member_id,$member_name,$password,$s_type=1)
    {
        //检查是否有该用户的token
        $mem_token = $this->getInfo([['member_id','=',$member_id],['s_type','=',1]],[],'mt_id,token');
        $data = [];
        $data['member_name'] = $member_name;
        $data['token'] = $this->get_token($member_name,$password);
        $data['lost_time'] = date('Y-m-d H:i:s', time() + TokenLostTime);
        if($mem_token){//修改
            $this->save($data,[['mt_id','=',$mem_token['mt_id']]]);
        }else{
            $data['member_id'] = $member_id;
            $data['s_type'] = $s_type;
            //存储新token
            $this->addInfo($data);
        }
        return $data['token'];
    }
    public function get_token($member_name,$password){
        return $token = md5($member_name.$password.$this->token_salt);
    }


}
