<?php
/**
 * 对接机构
 */
namespace app\api\controller;

use app\home\model\FormModel;
use app\home\model\HpvModel;
use app\lib\Log;
use think\Controller;
use think\Db;

//class Mechanism extends Controller
class Mechanism extends CheckShop
{
    private $model_form = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_form = new FormModel();
        $this->model_hpv = new HpvModel();
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
     * 生成测试的sign
     */
    public function test_sign(){
        $shopsecret = '1pvqe2ebziiq0gwn0upw7g3025t3wxt5';
        $sign = getSign($_POST, $shopsecret);
        return $sign;
    }
    /**
     * 添加针剂记录/更改状态
     */
    public function add_injection()
    {
        try{
            $post_error = parameter_check(['f_id','type','f_phone','f_date','f_time'],1);
            if($post_error['code'] == 300){
                throw new \Exception($post_error['message']);
            }
            $data = $post_error['data'];
            $data['status'] = input('post.status');
            $data['finish_time'] = input('post.finish_time');
            $form_info = $this->model_form->getInfo([['f_id','=',$data['f_id']]]);
            if(!$form_info)throw new \Exception('找不到该表单');

            Db::startTrans();
            //是否为HPV预约
            if(stripos($form_info['f_project'],'hpv') !== false){
                //检查是否已经存在记录
                $hpv = $this->model_hpv->getInfo([['from_id','=',$data['f_id']],['hpv_num','=',$data['type']]]);
                if($hpv){
                    $data['hpv_date'] = $data['f_date'];
                    $data['hpv_time'] = $data['f_time'];
                    $this->model_hpv->allowField(['hpv_date','hpv_time','status','finish_time'])->save($data,[['hpv_id','=',$hpv['hpv_id']]]);
                }else{
                    //增加HPV记录
                    $res = $this->model_hpv->add_hpv($data['f_id'], $form_info['f_name'], $data['f_phone'], $data['type'], $data['f_date'], $data['f_time']);
                    if(!$res){
                        throw new \Exception('1错误');
                    }
                }
            }
            if($data['type'] == 1) {
                //插入数据
                $res = $this->model_form->allowField(['f_date', 'f_time'])->save($data, [['f_id', '=', $data['f_id']]]);
                if (!$res) throw new \Exception('错误');
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $err_arr = return_info(2000,$e->getMessage().$e->getLine());
            $yz_log = new Log();
            $yz_log->log_entry('add_injection错误',$err_arr,'jigou');//将接收到的原始数据记录日志
            return $err_arr;
        }

        return return_info(0,'操作成功');
    }

}