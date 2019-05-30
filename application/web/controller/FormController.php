<?php

namespace app\web\controller;

use think\Controller;
use think\Db;
use app\lib\rsa_key\Rsakey;
use app\member\model\MemberTokenModel;

class FormController extends Controller
{
    private $overdue_time = 10; //参数加密多久过期 10s
    /**
     * 统一处理请求的数据
     * 检查权限
     */
    public function __construct()
    {
        // 检查加密数据
//        try{
//            $this->check_secret();
//        }catch (\Exception $e){
////            die(json_encode(['code'=>300,'message'=>'参数解析错误'],JSON_UNESCAPED_UNICODE));
//            die(json_encode(['code'=>300,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE));
//        }

        $request = request();
        $action_name = $request->action();
        if(!in_array($action_name,[
            //方法    不需要检查的过滤数组
            'login_form',
            'verification_code',
            'check_captcha',
            'send_sms',
        ])){
            //检查token
            $token_info = $this->check_token();

            $this->loginphone = $token_info['member_name'];
        }

    }
    /**
     * 检查加密数据
     */
    public function check_secret(){
        $rsa_key = new Rsakey();
        if (empty($_POST['secret'])){
            throw new \Exception('secret数据错误');
        }
        $val = $rsa_key->PrivateDecrypt($_POST['secret']);
        if(empty($val)){
            throw new \Exception('加密信息有误');
        }
        //检查过期时间
        if($val['timestamp'] + $this->overdue_time < time()){
            throw new \Exception('请求过期');
        }
        unset($val['timestamp']);
        $_POST = json_decode($val,true);
    }
    /**
     * 检查token
     */
    public function check_token(){
        $model_member_token = new MemberTokenModel();

        if (empty($_SERVER['HTTP_SESS'])) {
//            return false;
//            return ['code' => "700", 'message' => "登录已失效，请重新登录"];
            die(json_encode(['code' => "700", 'message' => "登录已失效，请重新登录"], JSON_UNESCAPED_UNICODE));
        }

        $token = $_SERVER['HTTP_SESS'];
        $where = [];
        $where[] = ['a.token','=',$token];
        $where[] = ['a.s_type','=',2];  //	1:管理账号，2:预约系统
        $where[] = ['a.lost_time','> time',time()];
        $info = $model_member_token->getInfo($where, [], 'member_id,member_name,s_type');
//        echo Db::getLastSql();
        if (!$info) {  //token已失效
//            return false;
//            return ['code' => "700", 'message' => "登录已过期，请重新登录"];
            die(json_encode(['code' => "700", 'message' => "登录已经过期，请重新登录"], JSON_UNESCAPED_UNICODE));
        }
        if($token != $model_member_token->get_token($info['member_name'],$info['member_id'].$info['s_type'])){  //  账号/密码 发生修改时需重新登录
//            return ['code' => "700", 'message' => "token已过期，请重新登录"];
            die(json_encode(['code' => "700", 'message' => "token已过期，请重新登录"], JSON_UNESCAPED_UNICODE));
        }
//        return true;
        return $info;
    }
}