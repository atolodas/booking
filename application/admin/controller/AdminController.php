<?php

namespace app\admin\controller;

use app\member\model\MemberTokenModel;
use think\Controller;
use think\Db;

class AdminController extends Controller
{
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
            $token_info = $this->check_token();
            if($token_info['code'] != 200){
                die(json_encode($token_info, JSON_UNESCAPED_UNICODE));
            }
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
        //	s_type登录类型 1：内部管理账号
        $info = $model_member_token->getInfo([['token','=',$_SERVER['HTTP_SESS']],['s_type','=',1],['lost_time','> time',time()]], [], 'member_id, member_name');
//        echo Db::getLastSql();
        if (!$info) {  //token已失效
//            return false;
            return ['code' => "700", 'message' => "登录已过期，请重新登录"];
        }
//        return true;
        return ['code' => "200", 'message' => ""];
    }
}