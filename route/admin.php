<?php

Route::rule('login','login/login'); //登录
Route::rule('set_password','login/set_password'); //登录

Route::rule('hospital_operation','hospital/hospital_operation');//添加修改医院
Route::rule('hospital_manage','hospital/hospital_manage');  //医院管理列表
Route::rule('hospital_list','hospital/hospital_list');  //医院列表
Route::rule('hospital_del','hospital/hospital_del'); //删除医院

Route::rule('product_time_operation','product/product_time_operation'); //添加修改产品
Route::rule('product_manage','product/product_manage'); //产品管理列表
Route::rule('product_del','product/product_del'); //删除产品
Route::rule('product_info','product/product_info'); //产品信息

Route::rule('form_add','form/form_add'); //添加表单
Route::rule('form_web','form/form_web'); //表单页面数据
Route::rule('get_bring_back','form/get_bring_back'); //根据订单号判断是否显示二三针带回
Route::rule('form_manage','form/form_manage'); //表单管理列表

Route::rule('order_manage','order/order_manage'); //订单管理列表


