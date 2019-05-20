<?php
namespace app\api\model;

use app\home\model\BookingModel;
use think\Db;

class ShopSecretModel extends BookingModel
{
    protected $table = 'bo_shop_secret';

    //生成 shopid 和 shopsecret
    public function create_shopid($shop_name){
        $this->shopid     = createNoncestr(18);
        $this->shopsecret   = createNoncestr(32);
        $this->shop_name    = $shop_name;
        return $this->save();
//         echo Db::getLastSql();
    }

}