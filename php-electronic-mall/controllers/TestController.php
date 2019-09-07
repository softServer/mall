<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/9/5
 * Time: 14:57
 */

namespace app\controllers;


use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use yii\helpers\VarDumper;

class TestController extends \yii\web\Controller
{
    public function actionQiniu()
    {
        $ak = 'XSusdHPRQMXaJOWdBVbUf8b2gobtkTSF5e0qZJG2';
        $sk = 'BBxcg2VDQHgXDCR3t_uQ3U4vV-KpKyUKMTdcHgAO';
        $bucket = 'luweiss';
        $auth = new Auth($ak, $sk);
        $token = $auth->uploadToken($bucket);
        $uploader = new UploadManager();
        list($res, $err) = $uploader->putFile($token, 'images/test.jpg', __DIR__ . '/test.jpg');
        VarDumper::dump($res, 3, 1);
        VarDumper::dump($err, 3, 1);
    }
}