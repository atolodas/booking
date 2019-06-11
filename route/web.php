<?php

Route::rule('get_bring_back','web/form/get_bring_back'); //根据订单号判断是否显示二三针带回
Route::rule('form_web','web/form/form_web'); //表单页面数据（废弃）

Route::rule('address_list','web/address/address_list'); //四级地址列表
Route::rule('login_form','web/form/login_form'); //登录预约系统
Route::rule('make_appointment','web/form/make_appointment'); //开始预约
Route::rule('appointment_date','web/form/appointment_date'); //预约日期和时间

