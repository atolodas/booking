<?php
namespace app\admin\model;

use app\home\model\BookingModel;

class AdminModel extends BookingModel
{

    protected $pk = 'admin_id';
    protected $table = 'bo_admin';


}