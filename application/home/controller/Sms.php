<?php

namespace app\home\controller;

use app\web\controller\FormController;
use think\Controller;
use think\Db;
use app\home\logic\Sms as logic_sms;
use app\home\model\SmsLogModel;
use think\captcha\Captcha;

//短信
class Sms extends FormController
{
    private $phone = '';
    private $logic_sms = '';
    public function __construct()
    {
        parent::__construct();
        $this->phone = input('post.phone');
        $this->logic_sms = new logic_sms;
    }
    /**
     * 获取图形验证码
     */
    public function verification_code(){
        $config =    [
            'length'      =>    4,// 验证码位数
            'fontSize'    =>    30,// 验证码字体大小
            'codeSet'    =>    '0123456789',// 设置验证码字符为纯数字
//            'imageH'    =>    0,//验证码图片高度，设置为0为自动计算
//            'imageW'    =>    0,//验证码图片宽度，设置为0为自动计算
//            'expire'    =>    1800,//默认三分钟
//            'reset'    =>    true,//验证成功后是否重置
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
    /**
     * 检查是否需要开启图形验证码
     **/
    public function check_captcha()
    {
        if (empty($this->phone)) {
            return return_info();
        }
        $res = $this->logic_sms->check_captcha($this->phone);
        if($res){
            return return_info(200, '需要开启');
        }else{
            return return_info(300, '不需要开启');
        }
    }
    /**
     * 短信发送接口
     **/
    public function send_sms()
    {
        if (empty($this->phone)) {
            return return_info();
        }
        //验证码安全检查  次数限制
        $error_log = $this->logic_sms->check_send_sms($this->phone);
        if (isset($error_log['code']) && $error_log['code'] != 200) {
            return $error_log;
        }
        $verify_code = rand(1000, 9999);
        $message_info = '验证码：' . $verify_code;
        $sl_array['sl_phone'] = $this->phone;
        $sl_array['sl_ip'] = getIp() ? getIp() : '';
        $sl_array['sl_captcha'] = $verify_code;
        $sl_array['sl_msg'] = $message_info;
        $sl_array['sl_type'] = 2;//2：手机验证码
        $sl_array['sl_msg_type'] = 1;//1：预约系统
        $sms_log = SmsLogModel::create($sl_array);
        $seqid = $sms_log->sl_id;
        $ret_data['code_id'] = $seqid;
        //发送验证码


        if ($seqid) {
            return return_info(200, '验证码已经发送', ["sl_id" => $seqid]);
        } else {
            return return_info(300, '发送失败，请稍后再试');
        }
    }

}
