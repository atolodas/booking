<?php
/**
 * 发送模板消息
 */
namespace app\home\logic;

class WxMessage
{
    private $appid = "wxefe017e41966f833";
    private $appsecret = "486042b9607befe24dd7889f7542dd2e";
    private $access_token = '';

    public function __construct()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
        $output = http_client($url);
        $jsoninfo = json_decode($output, true);
        $this->access_token = $jsoninfo["access_token"];
    }
    public function send_template_message($data=[]){
        $info = '{
           "touser":"oSPtW5zovtKkbAkC1Ck6A693Gs8E",
           "template_id":"-zvyo3JffW6LdwS6k-oakfhW0NorIS8rWtgKJlQUQYY",
           "data":{
                   "first": {
                       "value":"'.$data['first'].'",
                       "color":"#173177"
                   },
                   "keyword1":{
                       "value":"'.$data['keyword1'].'",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"'.$data['keyword2'].'",
                       "color":"#173177"
                   },
                   "keyword3": {
                       "value":"'.$data['keyword3'].'",
                       "color":"#173177"
                   },
                   "keyword4": {
                       "value":"'.$data['keyword4'].'",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"感谢您的信赖，请准时就诊。",
                       "color":"#173177"
                   }
           }
        }';
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->access_token;
        $result = http_client($url, $info);
        return $result;
    }


}