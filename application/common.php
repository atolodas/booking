<?php
use think\Db;
use app\lib\Excel;
/**
 * 返回信息
 * @param $code 200：成功  300：失败
 * @param $message
 * @param $data
 */
function return_info($code = '300', $message = '信息错误', $data = null)
{
    $arr['code'] = $code;
    $arr['message'] = $message;

    if ($data !== null) {
        $arr['data'] = $data;
    }
    $request = request();
    $form_token = $request->param('form_token');
    if(strlen($form_token) == 32){
//        if($code == 200){
//            //执行成功删除表单令牌
//            \think\Db::name('form_token')->where('form_token','=',$form_token)->delete();
//        }else{
//            //执行失败 重置表单令牌 允许表单再次提交
//            \think\Db::name('form_token')->where('form_token','=',$form_token)->update(['status'=>1]);
//        }
    }
    return $arr;
}
/**
 * 参数检查 用于新增数据
 * @param $arr  需要接收的字段的数组集合
 * @param $type 0：字段是否存在；1：需要判断是否为空
 * @return mixed
 */
function parameter_check($arr, $type = 0){
    $arr = array_flip($arr);    //键值反转
    $arr_data = array_intersect_key($_POST, $arr);  //获取数组中所需元素组成新的数组，用来安全接受数据
    if($type == 1){ //去除空值，用于判断数据是否为空
        $arr_data = array_filter($arr_data);    //去除false，null，''，0
    }
    //array_diff_key() 返回一个数组，该数组包括了所有出现在 array1 中但是未出现在任何其它参数数组中的键名的值。
    $arr_data_check = array_diff_key($arr, $arr_data);   //数组比较返回差值
    //检查返回所缺参数
    if(count($arr_data_check) > 0){
        $error_message = implode(',',array_keys($arr_data_check));
        return return_info(300, $error_message.'参数异常,请检查表单');
    }
    return return_info(200, '验证通过', $arr_data);
}
/**
 * 根据数组生成excel
 * @param array $data   //第一行
 * @param array $arr    //处理过的需要导出的数据
 * @param string $title     //文件名
 */
function createExcel($data = [], $arr = [], $title = '') {
    $excel_obj = new Excel();
    $excel_data = [];
    //设置样式
    $excel_obj->setStyle(['id' => 's_title', 'Font' => ['FontName' => '宋体', 'Size' => '12', 'Bold' => '1']]);
    //header
    foreach ($data as $v){
        $excel_data[0][] = ['styleid' => 's_title', 'data' => $v];
    }
    foreach ($arr as $k => $v) {
        $tmp = [];
        foreach ($v as $value){
            $tmp[] = ['data' => $value];
        }
        $excel_data[] = $tmp;
    }
    $excel_data = $excel_obj->charset($excel_data, 'utf-8');
    $excel_obj->addArray($excel_data);
    $excel_obj->addWorksheet($excel_obj->charset($title, 'utf-8'));
    $excel_obj->generateXML($excel_obj->charset($title, 'utf-8') . '-' . date('Y-m-d-H', time()));
}
/**
 * 获取当前IP
 * @return string 字符串类型的返回结果
 */
function getIp() {
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                return $ip;
                //会过滤掉保留地址和私有地址段的IP，例如 127.0.0.1会被过滤
                //也可以修改成正则验证IP
                /**
                if ((bool) filter_var($ip, FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 |
                FILTER_FLAG_NO_PRIV_RANGE |
                FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
                }* */
            }
        }
    }
    return null;
}
/**构造http请求  目前只支持get,post
 * @param $url
 * @param array $post_data  post数据
 * @param array $header_data   请求头数据
 * @param bool $json   是否使用json格式发送post数据
 * @return mixed
 */
function http_client($url,$post_data = [],$header_data = [],$json = false){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_TIMEOUT,60);
    curl_setopt($ch,CURLOPT_URL,$url);
    //判断https请求和http请求
    if(substr($url , 0 , 5) == 'https'){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    //post json格式数据
    if(count($post_data) > 0){
        if($json && is_array($post_data)){
            $post_data = json_encode($post_data);
        }
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
    }
    //这种post的数据类型
    if($json){
        $header_data[] = 'Content-Type: application/json; charset=utf-8';
        $header_data[] = 'Content-Length:' . strlen($post_data);
    }
    if(count($post_data > 0)){
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header_data);
    }
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//    curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1"); //代理服务器地址
//    curl_setopt($ch, CURLOPT_PROXYPORT, 8888); //代理服务器端口
    $res_data = curl_exec($ch);
    curl_close($ch);

    return $res_data;
}