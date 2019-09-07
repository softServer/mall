<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\models\common;


use app\models\FormId;

class CommonFormId
{
    public static function save($formIdList)
    {
        $formIdList = \Yii::$app->serializer->decode($formIdList);
        foreach ($formIdList as $item) {
            if (!empty($item['form_id'])) {
                FormId::addFormId([
                    'store_id' => \Yii::$app->store->id,
                    'user_id' => \Yii::$app->user->id,
                    'wechat_open_id' => \Yii::$app->user->identity->wechat_open_id,
                    'form_id' => $item['form_id'],
                    'type' => 'form_id'
                ]);
            }
        }

        return true;
    }
}