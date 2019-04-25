<?php
namespace app\home\model;
use think\Model;

class HospitalModel extends BookingModel
{
    protected $pk = 'h_id';
    protected $table = 'bo_hospital';

}