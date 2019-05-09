<?php
/**
 * 应用开始标签位
 */
namespace app\home\behavior;

use think\Request;

class app_begin
{
    public function run(Request $request){
        if($request->isOptions()){
            header("Content-type: text/html; charset=utf-8");
            die(json_encode(['code' => "200", 'message' => "距离小兵到达战场还有30s"], JSON_UNESCAPED_UNICODE));
        }
    }


}