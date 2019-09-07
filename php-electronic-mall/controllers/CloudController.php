<?php
/**
 * @copyright ©2018
 * @author Lu Wei
 * @link
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/23 14:02
 */


namespace app\controllers;


use app\hejiang\cloud\Cloud;
use app\hejiang\cloud\CloudApi;
use app\hejiang\cloud\Config;

class CloudController extends Controller
{
    public $layout = '@app/views/cloud/layout';

    public function beforeAction($action)
    {
        if ($action->id === 'test-site') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $res = Cloud::getHostInfo();
        $res['remoteTestSiteUrl'] = Config::BASE_URL . CloudApi::TEST_SITE;
        if ($res['code'] === 0) {
            $res['localTestSiteUrl'] = $res['data']['host']['protocol'] . $res['data']['host']['domain'] . \Yii::$app->urlManager->createUrl(['cloud/test-site']);
        } else {
            $res['localTestSiteUrl'] = '';
        }
        return $this->render('index', $res);
    }

    public function actionTestSite()
    {
        return \Yii::$app->request->post('data');
    }
}