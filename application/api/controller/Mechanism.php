<?php
/**
 * 对接机构
 */
namespace app\api\controller;

use app\home\model\FormModel;
use think\Controller;
use app\lib\rsa_key\Rsakey;

//class Mechanism extends Controller
class Mechanism extends CheckShop
{
    private $model_form = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_form = new FormModel();
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
    public function injection_add(){

        var_dump($this->post_data);
        $post_data = $this->post_data;
        $data['f_id'] = $post_data['f_id'];
        $data['f_date'] = $post_data['f_date'];
        $data['f_time'] = $post_data['f_time'];
        $data['f_phone'] = $post_data['f_phone'];
        //插入数据
        $res = $this->model_form->save($data);
        if($res){
            return return_info(200,'操作成功');
        }else{
            return return_info(300,'操作失败');
        }
    }
    public function injection_status(){
        $res = $this->model_form->save([],['']);

    }

}