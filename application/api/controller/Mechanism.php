<?php
/**
 * 对接机构
 */
namespace app\api\controller;

use think\Controller;
use app\lib\rsa_key\Rsakey;

class Mechanism extends Controller
//class Mechanism extends CheckShop
{
    public function __construct()
    {
        parent::__construct();
    }
    //生成 shopid 和 shopsecret
//    public function create_shopid(){
//        echo ':)';exit;
//        $model_shop_secret = new ShopSecretModel();
//        $shop_name = input('post.shop_name');
//        if (empty($shop_name)){
//            return false;
//        }
//        return $model_shop_secret->create_shopid($shop_name);
//    }
    /**
     * 添加针剂记录/更改状态
     */
    public function add_injection(){

        $rsa_key = new Rsakey();
//        $str = $rsa_key->public_encrypt(json_encode($_POST));
//        echo $str;
//        echo $rsa_key->private_decrypt($str);
        $str = $rsa_key->PublicEncrypt($_POST);
        echo $str;
        echo $rsa_key->PrivateDecrypt($str);
        exit;
        var_dump($this->post_data);
        $post_data = $this->post_data;
        $data['f_date'] = $post_data['f_date'];
        $data['f_time'] = $post_data['f_time'];
        $data['f_phone'] = $post_data['f_phone'];
        //插入数据
    }
    /**
     * 其它针剂的记录/更改记录
     */
    public function other_injection(){

    }

}