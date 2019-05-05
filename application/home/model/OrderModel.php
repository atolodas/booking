<?php
namespace app\home\model;

class OrderModel extends BookingModel
{
    protected $pk = 'o_id';
    protected $table = 'bo_order';

}