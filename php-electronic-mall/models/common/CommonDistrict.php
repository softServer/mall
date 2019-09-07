<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\models\common;

use app\models\DistrictArr;

class CommonDistrict
{
    public function search()
    {
        $cache_key = md5('district');
        $cache_data = \Yii::$app->cache->get($cache_key);
        if ($cache_data && false) {
            $province_list = $cache_data;
        }else{
            $d = new DistrictArr();
            $arr= $d->getArr();
            $province_list = $d->getList($arr);
            \Yii::$app->cache->set($cache_key, $province_list, 86400 * 7);
        }
        return $province_list;
    }
}