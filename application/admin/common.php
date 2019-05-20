<?php
/**
 * 根据年月日查询查询时间区间
 */
function getQueryTime($time_field,$query_start_time,$query_end_time){
    $where = array();
    if ($query_start_time && $query_end_time) {
        $where[] = [$time_field, 'between time', [$query_start_time, strtotime('+1day',strtotime($query_end_time))-1]];
    } elseif ($query_start_time) {
        $where[] = [$time_field,'egt', $query_start_time];
    } elseif ($query_end_time) {
        $where[] = [$time_field,'elt', $query_end_time];
    }
    return $where;
}