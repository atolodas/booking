<?php
/**
 * 根据身份证号码获取信息
 */

namespace app\lib;


class Idcard
{
    /**
     *  根据身份证号码获取性别
     *  author:xiaochuan
     *  @param string $idcard    身份证号码
     *  @return int $sex 性别 1男 2女 0未知
     */
    public function get_sex($idcard) {
        if(empty($idcard)) return null;
        $sexint = (int) substr($idcard, 16, 1);
//        return $sexint % 2 === 0 ? '女' : '男';
        return $sexint % 2 === 0 ? '2' : '1';
    }

    /**
     *  根据身份证号码获取生日
     *  author:xiaochuan
     *  @param string $idcard    身份证号码
     *  @return $birthday
     */
    public function get_birthday($idcard) {
        if(empty($idcard)) return null;
        $bir = substr($idcard, 6, 8);
        $year = (int) substr($bir, 0, 4);
        $month = (int) substr($bir, 4, 2);
        $day = (int) substr($bir, 6, 2);
        return $year . "-" . $month . "-" . $day;
    }

    /**
     *  根据身份证号码计算年龄
     *  author:xiaochuan
     *  @param string $idcard    身份证号码
     *  @return int $age
     */
    public function get_age($idcard){
        if(empty($idcard)) return null;
        #  获得出生年月日的时间戳
        $date = strtotime(substr($idcard,6,8));
        #  获得今日的时间戳
        $today = strtotime('today');
        #  得到两个日期相差的大体年数
        $diff = floor(($today-$date)/86400/365);
        #  strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($idcard,6,8).' +'.$diff.'years')>$today?($diff+1):$diff;
        return $age;
    }
    /**
     *  判断字符串是否是身份证号
     *  author:xiaochuan
     *  @param string $idcard    身份证号码
     */
    public function isIdCard($idcard){
        #  转化为大写，如出现x
        $idcard = strtoupper($idcard);
        #  加权因子
        $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        #  按顺序循环处理前17位
        $sigma = 0;
        #  提取前17位的其中一位，并将变量类型转为实数
        for ($i = 0; $i < 17; $i++) {
            if(!isset($idcard{$i}))return false;
            $b = (int)$idcard{$i};
            #  提取相应的加权因子
            $w = $wi[$i];
            #  把从身份证号码中提取的一位数字和加权因子相乘，并累加
            $sigma += $b * $w;
        }
        #  计算序号
        $sidcard = $sigma % 11;
        #  按照序号从校验码串中提取相应的字符。
        $check_idcard = $ai[$sidcard];
        if ($idcard{17} == $check_idcard) {
            return true;
        } else {
            return false;
        }
    }
}