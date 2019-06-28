<?php
namespace app\member\model;

use app\home\model\BookingModel;

class RegistrationModel extends BookingModel
{
    protected $pk = 'id';
    protected $table = 'bo_registration';



}
