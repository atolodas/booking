<?php
/**
 * 检查机构
 */
namespace app\api\controller;

use think\Controller;
use app\api\model\ShopSecretModel;
use app\lib\Log;

class CheckShop extends Controller
{
    protected $post_data = [];
    protected $shop_code = '';
    protected $overdue_time = 10; //签名多久过期 10s
    public function __construct()
    {
        $yz_log = new Log();
        $yz_log->log_entry('机构原始数据'.$_SERVER['REMOTE_ADDR'],$_POST,'jigou');//将接收到的原始数据记录日志

        $post_data = $_POST;
        try {
            if(!isset($post_data['shopid'])|| !isset($post_data['sign']) || !isset($post_data['timestamp'])){
                throw new \Exception(1001);
            }
            //检查 密码
            $model_shop_secret = new ShopSecretModel();
            $shop = $model_shop_secret->getInfo([['shopid','=',addslashes($post_data['shopid'])]],[],'shopsecret,shop_code');
            if(empty($shop['shopsecret'])){
                throw new \Exception(1002);
            }
            //检查 签名
            $post_sign = $post_data['sign'];
            unset($post_data['sign']);
            $sign = getSign($post_data, $shop['shopsecret']);
            if ($sign != $post_sign){
                throw new \Exception(1003);
            }
            //检查过期时间
            if($post_data['timestamp'] + $this->overdue_time < time()){
                throw new \Exception(1004);
            }
            unset($post_data['timestamp']);
        } catch (\Exception $e) {
            //记录错误日志
            $mess = $e->getMessage();
            $yz_log->log_entry('错误信息',$mess,'jigou');//记录错误信息

            if(strlen($mess)==4){
                die(json_encode(['code'=>$mess,'message'=>'非法请求'],JSON_UNESCAPED_UNICODE));
            }else{
                die(json_encode(['code'=>2001,'message'=>'出错啦'],JSON_UNESCAPED_UNICODE));
//                die(json_encode(['code'=>2001,'message'=>$mess],JSON_UNESCAPED_UNICODE));
            }
        }
        $this->shop_code = $shop['shop_code'];
        $this->post_data = $post_data;
    }

}