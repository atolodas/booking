<?php
/**
 * 收货地址
 */
namespace app\web\controller;

use app\home\model\AddressModel;
use think\Controller;
use think\Db;

class Address extends Controller
{
    private $model_address = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_address = new AddressModel();
    }
    /**
     * 四级地址列表
     */
    public function address_list()
    {
        $parent = input('get.parent') ? input('get.parent') : 0;
        $con = [['parent','=',$parent]];

        $res = $this->model_address->getListInfo($con,[],'code,name,level','code asc');
//        echo Db::getLastSql();
        return return_info(200,'地址列表',$res);
    }

}