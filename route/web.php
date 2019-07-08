<?php

Route::rule('get_bring_back','web/form/get_bring_back'); //根据订单号判断是否显示二三针带回
Route::rule('form_web','web/form/form_web'); //表单页面数据（废弃）

Route::rule('address_list','web/address/address_list'); //四级地址列表
Route::rule('login_form','web/form/login_form'); //登录预约系统

Route::rule('get_orders','web/form/get_orders'); //开始预约
Route::rule('make_appointment','web/form/make_appointment'); //开始预约
Route::rule('appointment_date','web/form/appointment_date'); //预约日期和时间
Route::rule('reservation_info','web/form/reservation_info'); //检查预约信息
Route::rule('reservation_time','web/form/reservation_time'); //根据产品获取预约时间
Route::rule('other_stitches','web/form/other_stitches'); //预约二三针
Route::rule('store_list','web/form/store_list'); //分店列表

