<?php

namespace app\home\behavior;


class app_begin
{
    public function run(){
        //这个阶段获取不到数据
//        //定义当前模块名
//        define('MODULE_NAME', request()->module());
//        //定义当前控制器名
//        define('CONTROLLER_NAME', request()->controller());
//        //定义当前方法名
//        define('ACTION_NAME', request()->action());

        //统一处理OPTIONS请求
//        if(isset($_SERVER['REQUEST_METHOD'])){
//            if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
//                //处理js修改请求头 需要进行预请求的响应问题的
//                die(json_encode(['code' => "200", 'message' => "距离小兵到达战场还有30s"], JSON_UNESCAPED_UNICODE));
//            }
//        }


    }


}