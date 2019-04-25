<?php
namespace app\home\model;
use think\Model;

class ProductTimeModel extends BookingModel
{
    protected $pk = 'pt_id';
    protected $table = 'bo_product_time';

}