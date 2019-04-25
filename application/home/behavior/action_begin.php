<?php

namespace app\home\behavior;




class action_begin
{
    //方法开始的钩子   进行token的检查
    public function run(){
//        $request = request();
//        $controller_action = $request->controller().'/'.$request->action();
//        //自定义请求头 的预请求处理
//        if(isset($_SERVER['REQUEST_METHOD'])){
//            if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
//                //处理js修改请求头 需要进行预请求的响应问题的
//                die(json_encode(['code' => "200", 'message' => "距离小兵到达战场还有30s"], JSON_UNESCAPED_UNICODE));
//            }
//        }
//        //定义请求常量  记录接受请求的时间
//        define('MODULE_NAME', strtolower($request->module()));
//        define('CONTROLLER_NAME', strtolower($request->controller()));
//        define('ACTION_NAME', strtolower($request->action()));
//        define('TIME_START', microtime(true));
//
//        if(\think\Db::name('attr')->where(['attr_name'=>'access_log'])->value('attr_value')){
//            $data['params'] = json_encode([MODULE_NAME, CONTROLLER_NAME, ACTION_NAME], JSON_UNESCAPED_UNICODE);
//            $data['data'] = json_encode(array_merge($_POST,$_GET), JSON_UNESCAPED_UNICODE);
//            $data['header'] = isset($_SERVER['HTTP_TOKEN'])?$_SERVER['HTTP_TOKEN']:'';
//            $data['addtime'] = time();
//            $table_num = date('ym',TIMESTAMP);
//            $GLOBALS['access_log_id'] = \think\Db::name('access_'.$table_num.'_log')->insertGetId($data);
//        }
//
//
//
//
//        $controller_action = CONTROLLER_NAME.'/'.ACTION_NAME;
//        if(in_array($controller_action,[
//            //控制器/方法    不需要做token检查的过滤数组
//            'login/login',
//            'member/is_bind_openid',
//            'member/bind_openid',
//            'reg/reg',
//            'home/home',
//            'home/login',
//            'goods/good',
//            'goodsevaluate/goods_evaluate_list',
//            'order/confirm_order',
//            'sms/send_sms',
//            'member/direct_subordinate',
//            'member/member_pwd',
//            'buy/order_check'
//        ]) || MODULE_NAME == 'admin' || CONTROLLER_NAME == 'weixin' || CONTROLLER_NAME == 'tool'){
//            //不需要检查token
//        }else{
//            //需要检查token
////                $header = $request->header();
////                $member_id = $request->param('member_id');
////                if ($member_id > 0 && isset($header['token'])) {
////                    //提交member_id时需要核对member_id
////                    $r_token = db('member_token')->where(['token' => $header['token'], 'member_id' => $member_id])->find();
////                } else {
////                    //无member_id是直接核对token
////                    if (isset($header['token'])) {
////                        $r_token = db('member_token')->where(['token' => $header['token']])->find();
////                    } else {
////                        $r_token = false;
////                    }
////                }
////                //检验失败
////                if(!$r_token){
////                    $this->die_700();
////                }
//        }
    }


    private function die_700(){
//        \think\Db::name('warning_log')->insert(['content'=>'error 登录已经过期，请重新登录','addtime'=>TIMESTAMP]);
//        header("Content-type: application/json; charset=utf-8");
//        die(json_encode(['code' => "700", 'message' => ""], JSON_UNESCAPED_UNICODE));
    }
}