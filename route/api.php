<?php

Route::rule('get_push_info','api/youzan_push/get_push_info'); //获取有赞推送信息

Route::rule('get_order','api/YouzanTest/get_order'); //获取有赞历史订单的数据
Route::rule('update_order','api/YouzanTest/update_order'); //更新订单数据
Route::rule('update_order1','api/YouzanTest/update_order1'); //更新订单数据

Route::rule('create_shopid','api/Mechanism/create_shopid'); //
Route::rule('test_sign','api/Mechanism/test_sign'); //

Route::rule('add_injection','api/mechanism/add_injection'); //添加预约记录

