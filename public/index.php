<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

header("Content-type: text/html; charset=utf-8");
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
// 响应头设置
//header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type,Accept,Authorization,sess');
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

define('BASE_ROOT_PATH',str_replace('\\','/',dirname(__FILE__)));
// 支持事先使用静态方法设置Request对象和Config对象
// 执行应用并响应
//$build = include '../build.php';//自动生成模块
//Container::get('build')->run($build);
Container::get('app')->run()->send();
