<?php
namespace app\lib\open;

class Youzan{
    private $api_version = '';//要调用的api版本号
    private $access_token = '';
    /**
     * 获得token
     * @return mixed
     */
    public function __construct(){
        require_once __DIR__ . '/lib/YZTokenClient.php';
        require_once __DIR__ . '/lib/YZGetTokenClient.php';
        require_once __DIR__ . '/YzConfig.php';
        $config = new \YzConfig();
        $this->api_version = $config->api_version;

        $token = new \YZGetTokenClient($config->app_id , $config->app_secret);
        $type = 'self';
        $keys['kdt_id'] = $config->kdt_id;
        $res = $token->get_token( $type , $keys );
        $this->access_token = $res['access_token'];
    }
    /**
     * 根据订单号获取订单详情
     */
    public function order_detail($order_sn=''){
        $client = new \YZTokenClient($this->access_token);
        $method = 'youzan.trade.get';//要调用的api名称
        $this->api_version = '4.0.0';
        $my_params = [
            'tid' => $order_sn,
        ];

        $response = $client->post($method, $this->api_version, $my_params);
        if(isset($response['response'])){
            return return_info(200,'',$response['response']);
        }else{
            return return_info(300,$response['error_response']);
        }
    }
}

