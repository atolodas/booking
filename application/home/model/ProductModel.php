<?php
namespace app\home\model;
use think\Model;

class ProductModel extends BookingModel
{
    protected $pk = 'p_id';
    protected $table = 'bo_product';

}