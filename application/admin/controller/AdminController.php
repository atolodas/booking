<?php

namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\member\model\MemberTokenModel;
use think\Controller;
use think\Db;

class AdminController extends Controller
{
    public $admin_id = '';
    public $admin_account = '';
    /**
     * 统一处理请求的数据
     * 检查权限
     */
    public function __construct()
    {
        $request = request();
        $action_name = $request->action();
        if(in_array($action_name,[
            //方法    不需要检查的过滤数组
            'login',
            'out_login',
        ])){
            //不需要检查

        }else{
//            $session = $this->check_session();
//            if(!$session){
//                die(json_encode(['code' => "700", 'message' => "1登录已过期，请重新登录"], JSON_UNESCAPED_UNICODE));
//            }
            //检查token
            //检查token
//            $token_info = $this->check_token();
//            if($token_info['code'] != 200){
//                die(json_encode($token_info, JSON_UNESCAPED_UNICODE));
//            }
//            $token_info = $token_info['data'];
//
//            $this->admin_id = $token_info['admin_id'];
//            $this->admin_account = $token_info['admin_account'];
            $this->admin_id = 1;
            $this->admin_account = 'admin';

        }
    }
    /**
     * 检查session（废弃）
     */
    public function check_session() {
        $nowtime = time();
        $s_time = session('admin_info.logintime');
        if (($nowtime - $s_time) > TokenLostTime) {
            $this->out_login();
            return false;
        }else{
            return true;
        }
//        else {
//            session('admin_info.logintime',$nowtime);
//        }
    }
    /**
     * 退出登录
     */
    public function out_login(){
        $model_member_token = new MemberTokenModel();
        if (!empty($_SERVER['HTTP_SESS'])) {
            $model_member_token->where([['token','=',$_SERVER['HTTP_SESS']]])->delete();
        }
    }
    /**
     * 检查token
     */
    public function check_token(){
        $model_member_token = new MemberTokenModel();

        if (empty($_SERVER['HTTP_SESS'])) {
//            return false;
            return ['code' => "700", 'message' => "登录已失效，请重新登录"];
        }

        $token = $_SERVER['HTTP_SESS'];
        $where = [];
        $where[] = ['a.token','=',$token];
        $where[] = ['a.s_type','=',1];  //	s_type登录类型 1：内部管理账号
        $where[] = ['a.lost_time','> time',time()];
        $where[] = ['b.status','=',1];
        $info = $model_member_token->getInfo($where, [['bo_admin b','a.member_id = b.admin_id']], 'b.admin_id, b.admin_account,b.admin_name,b.admin_password');
//        echo Db::getLastSql();
        if (!$info) {  //token已失效
//            return false;
            return ['code' => "700", 'message' => "登录已过期，请重新登录"];
        }
        if($token != $model_member_token->get_token($info['admin_account'],$info['admin_password'])){  //  账号/密码 发生修改时需重新登录
            return ['code' => "700", 'message' => "token已过期，请重新登录"];
        }
//        return true;
        return ['code' => "200", 'data' => $info];
    }
}