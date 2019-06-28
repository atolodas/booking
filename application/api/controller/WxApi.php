<?php

namespace app\api\controller;

class WxApi
{
    /**
     * 发送模板消息
     */
    public function send_notice(){
        //获取access_token
        if ($_COOKIE['access_token']){
            $access_token2=$_COOKIE['access_token'];
        }else{
            $json_token=$this->curl_post("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret.'");
            $access_token1=json_decode($json_token,true);
            $access_token2=$access_token1['access_token'];
            setcookie('access_token',$access_token2,7200);
        }
        //模板消息
        $json_template = $this->json_tempalte();
        $url="https://api.weixin.qq.com/cgi- bin/message/template/send?access_token=".$access_token2;
        $res=$this->curl_post($url,urldecode($json_template));
        if ($res['errcode']==0){
            return '发送成功';
        }else{
            return '发送失败';
        }
    }
    /**
     * 将模板消息json格式化
     */
    public function json_tempalte(){
        //模板消息
        $template=array(
            'touser'=>'.$openid.',  //用户openid
            'template_id'=>".$tenpalate_id.", //在公众号下配置的模板id
            'url'=>".$uel.", //点击模板消息会跳转的链接
            'topcolor'=>"#7B68EE",
            'data'=>array(
                'first'=>array('value'=>urlencode("您的活动已通过"),'color'=>"#FF0000"),
                'keyword1'=>array('value'=>urlencode('测试文章标题'),'color'=>'#FF0000'),  //keyword需要与配置的模板消息对应
                'keyword2'=>array('value'=>urlencode(date("Y-m-d H:i:s")),'color'=>'#FF0000'),
                'keyword3'=>array('value'=>urlencode('测试发布人'),'color'=>'#FF0000'),
                'keyword4'=>array('value'=>urlencode('测试状态'),'color'=>'#FF0000'),
                'remark' =>array('value'=>urlencode('备注：这是测试'),'color'=>'#FF0000'), )
        );
        $json_template=json_encode($template);
        return $json_template;
    }
    /**
     * @param $url
     * @param array $data
     * @return mixed
     * curl请求
     */
    public function curl_post($url , $data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}