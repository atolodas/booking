<?php

namespace app\api\controller;


class ProductCode extends CheckShop
{
    public function update_product(){
        $data['pro_title'] = $this->post_data[''];
        $data['pro_code'] = $this->post_data[''];
        $data['pro_shop'] = $this->post_data['shopid'];
    }
}