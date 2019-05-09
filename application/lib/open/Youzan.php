<?php
namespace app\lib\open;

class Youzan{
    private $access_token = '';
    private $client = [];
    private $my_params = [];
    private $api_version = '';//要调用的api版本号
    private $method = '';
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
        $this->client = new \YZTokenClient($this->access_token);
    }
    /**
     * 根据订单号获取订单详情
     */
    public function youzan_order_detail($order_sn=''){
        $this->method = 'youzan.trade.get';//要调用的api名称
        $this->api_version = '4.0.0';
        $this->my_params = [
            'tid' => $order_sn,
        ];
        return $this->to_output();
    }
    /**
     * 获取订单列表
     */
    public function youzan_order_list($my_params = []){
        $this->method = 'youzan.trades.sold.get';//要调用的api名称
        $this->api_version = '4.0.0';
        $this->my_params = $my_params;
        return $this->to_output();
    }

    /**
     * 请求api返回数据
     */
    private function to_output(){
        $response = $this->client->post($this->method, $this->api_version, $this->my_params);
        //记录返回日志，数量太大，有需要再开启日志
//        $yz_log = new \app\lib\Log();
//        $yz_log->log_entry('请求api返回数据',$response);//将接收到的原始数据记录日志

        if(isset($response['response'])){
            return return_info(200,'',$response['response']);
        }else{
            return return_info(300,$response['error_response']);
        }
    }
}

