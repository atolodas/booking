<?php

namespace app\home\logic;

use app\home\model\SmsLogModel;
use app\home\model\AttrModel;
use think\Db;

class Sms
{
    /**
     * 检查是否需要开启图形验证码
     **/
    public function check_captcha($phone)
    {
        //多次以后获取验证码需要输入图形验证码
        $last_code = $this->check_day_num($phone);
        if ($last_code['num'] >= 2) { //第二次以后获取验证码需要输入图形验证码
            return true;
//            return return_info(200, '需要开启');
        }
        return false;
//        return return_info(300, '不需要开启');
    }
    /**
     * 验证码安全检查  次数限制
     */
    public function check_send_sms($phone)
    {
        $model_attr = new AttrModel();
        $ua = $_SERVER['HTTP_USER_AGENT'];
//        $attr_list = $model_attr->where([['attr_name','=','sms_lj']])->whereOr([['attr_name','=','max_sms_num']])->field('attr_value')->find();
        $attr_list = $model_attr->whereIn('attr_name',['sms_lj','max_day_sms_num','max_hour_sms_num'])->field('attr_name,attr_value')->select()->toArray();
//        echo Db::getLastSql();
        $attr_list = array_reduce($attr_list, create_function('$v,$w', '$v[$w["attr_name"]]=$w["attr_value"];return $v;'));
//        var_dump($attr_list);
        $sms_lj = json_decode($attr_list['sms_lj']);
//        foreach ($sms_lj as $name => $value) {
//            //Mozilla/4.0  Firefox/27.0  Mozilla/5.0  Gecko/20100101
//            if (strstr($ua, $value)) {
////                $data = '代理检查拦截:' . $data;
////                $this->addInfo(['str' => $data, 'addtime' => $addtime]);
//                return ['code' => 300, 'message' => '禁止代理'];
//            }
//        }
        //查找当天该号码发送短信数，和最后发送的那条短信记录，若次数过多则冻结一个小时
        $last_code = $this->check_day_num($phone);
        //当前时间前一个小时
        $old_time = date('Y-m-d H:i:s',time() - 60 * 60);
        if (isset($attr_list['max_day_sms_num']) && $last_code['num'] >= $attr_list['max_day_sms_num']) {
            return ['code' => 300, 'message' => '该号码今日发送次数过多,已冻结'];
        }
        //如果 在一个小时之内 数量超过限制
        if (isset($attr_list['max_hour_sms_num']) && $last_code['num'] >= $attr_list['max_hour_sms_num'] && $last_code['create_time'] > $old_time) {
//                $data = '数量限制拦截:' . $data;
//                $this->addInfo(['str' => $data, 'create_time' => time()]);
            return ['code' => 300, 'message' => '该号码今日发送次数过多,已冻结一小时'];
        }
    }
    /**
     * 查找当天该号码发送短信数
     */
    public function check_day_num($phone){
        $model_sms_log = new SmsLogModel();
        $condition = [];
        $condition[] = ['sl_phone', '=', $phone];
        //查找当天该号码发送短信数，和最后发送的那条短信记录，若次数过多则冻结一个小时
        $last_code = $model_sms_log->whereTime('create_time', 'today')->where($condition)->field('count(*) as num,MAX(create_time) as create_time')->find();
//        echo Db::getLastSql();
        return $last_code;
    }
    /**
     * 检查手机验证码
     * @param $sl_id
     * @param $sl_phone
     * @param $sl_code
     * @return array
     */
    public function sms_check($sl_id,$sl_phone,$sl_code){

        $model_sms_log = new SmsLogModel;
        $sms_log = $model_sms_log->get($sl_id);
        if(!$sms_log){
            return ['code'=>300,'message'=>'验证码id出错'];
        }
        if($sms_log->sl_phone != $sl_phone){
            return ['code'=>300,'message'=>'验证码和手机号不匹配'];
        }
        if($sms_log->sl_code != $sl_code){
            return ['code'=>300,'message'=>'验证码输入错误'];
        }
        if(strtotime($sms_log->create_time) + 60*30 < time()){
            return ['code'=>300,'message'=>'验证码已失效,有效时间半个小时'];
        }
        return ['code'=>200,'message'=>'验证码正确','data'=>$sms_log->toArray()];
    }


}