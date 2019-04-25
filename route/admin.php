<?php

Route::rule('admin/hospital_operation','admin/hospital/hospital_operation');//添加修改医院
Route::rule('admin/hospital_manage','admin/hospital/hospital_manage');  //医院管理列表
Route::rule('admin/hospital_list','admin/hospital/hospital_list');  //医院列表
Route::rule('admin/hospital_del','admin/hospital/hospital_del'); //删除医院
Route::rule('admin/product_time_operation','admin/product/product_time_operation'); //添加修改产品
Route::rule('admin/product_manage','admin/product/product_manage'); //产品管理列表
Route::rule('admin/product_del','admin/product/product_del'); //删除产品
Route::rule('admin/product_info','admin/product/product_info'); //产品信息
