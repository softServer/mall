<?php

use Hejiang\Storage\Helpers\UrlConverter;

define('YII_DEBUG', true);

require_once '../vendor/autoload.php';
require_once '../vendor/yiisoft/yii2/Yii.php';

$storageConfig = [
    'class' => 'Hejiang\Storage\Components\StorageComponent',
    'basePath' => 'temp/'
];

$app = new \yii\web\Application(
    [
        'id' => 'basic',
        'language' => 'zh-CN',
        'timeZone' => 'Asia/Shanghai',
        'basePath' => __DIR__,
        'components' => [
            'storage' => $storageConfig
        ]
    ]
);

/** @var \Hejiang\Storage\Components\StorageComponent */
$storage = \Yii::$app->storage;

/** @var \Hejiang\Storage\Drivers\BaseDriver */
$storage->setDriver([
    'class' => 'Hejiang\Storage\Drivers\Local',
    // 'urlCallback' => new UrlConverter('https://replaced-domain.com?replaced-param=foo'),
]);

/** @var \Hejiang\Storage\UploadedFile */
$file = $storage->getUploadedFile('file');

$res = $file->saveAsUniqueHash();

var_dump($res);