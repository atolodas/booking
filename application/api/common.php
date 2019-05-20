<?php
/**
 * 产生随机字符串
 * @param int $length   最长长度
 * @return string
 */
function createNoncestr($length = 18)
{
    $chars ='abcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    for($i = 0; $i <$length; $i ++){
        $str .= substr($chars,mt_rand(0,strlen($chars)-1),1);
    }
    return $str;
}
/**
 * 生成哈希签名
 * @param $array
 * @param $secret
 * @return string
 */
function getSign($array, $shopsecret) {
    ksort($array);
    $requestString = '';
    foreach ($array as $k => $v) {
        $requestString .= $k . '=' . urlencode($v);
    }
    $newSign = hash_hmac("md5", strtolower($requestString), $shopsecret);
    return $newSign;
}