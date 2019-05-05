<?php
/**
 * 写入日志
 */

namespace app\lib;


class Log
{
    /**
     * 录入日志
     */
    public function log_entry($msg,$content) {

        $request = request();
        $filename = BASE_ROOT_PATH.'/../runtime/youzan_log/' . $request->action() . '_' . date('Ymd') . '.log';

        if (is_array($content)) {
            $context = var_export($content,true);
        } else {
            $context = preg_replace('/[ \t\r\n]+/', ' ', $content);
        }
        $log = [
                    '[' . date('Y-m-d H:i:s') . ']  '.$_SERVER["PHP_SELF"]."    $msg",
                    '------start------',
                    $context,
                    '------end------'
               ];
        $log = implode(PHP_EOL,$log);

        file_put_contents($filename, $log . PHP_EOL, FILE_APPEND);
    }
}