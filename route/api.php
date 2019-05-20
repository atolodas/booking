<?php

Route::rule('get_push_info','api/youzan_push/get_push_info'); //获取有赞推送信息

Route::rule('get_order','api/YouzanTest/get_order'); //获取有赞历史订单的数据
Route::rule('update_order','api/YouzanTest/update_order'); //更新订单数据

Route::rule('add_injection','api/Mechanism/add_injection'); //
Route::rule('create_shopid','api/Mechanism/create_shopid'); //
