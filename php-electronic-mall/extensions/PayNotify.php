<?php
/**
 * @copyright ©2018
 * @author Lu Wei
 * @link
 * Created by IntelliJ IDEA
 * Date Time: 2018/7/5 10:32
 */


namespace app\extensions;


class PayNotify
{
    public static function getHostInfo()
    {
        $host_info = \Yii::$app->request->hostInfo;
        $protocol = env('PAY_NOTIFY_PROTOCOL', false);
        if ($protocol === 'http') {
            $host_info = str_replace('https:', 'http:', $host_info);
        }
        if ($protocol === 'https') {
            $host_info = str_replace('http:', 'https:', $host_info);
        }
        return $host_info;
    }
}