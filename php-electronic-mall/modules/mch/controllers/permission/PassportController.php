<?php

/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\modules\mch\controllers\permission;

use app\controllers\Controller;
use app\modules\mch\models\MchLoginForm;
use yii\captcha\CaptchaAction;
use Yii;

class PassportController extends Controller
{
    public $layout = 'passport';

    public function actions()
    {
        return [
            'captcha' => \app\utils\ClearCaptchaAction::className(),
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->mchRoleAdmin->isGuest) {
            return $this->render('login');
        } else {
            Yii::$app->session->set('store_id', Yii::$app->mchRoleAdmin->identity->store_id);
        }

        Yii::$app->response->redirect(Yii::$app->urlManager->createUrl(['mch/store/index']))->send();
    }

    public function actionLogin()
    {
        $form = new MchLoginForm();
        $form->attributes = Yii::$app->request->post();
        return $form->login();
    }

    public function actionLogout()
    {
        $url = $_COOKIE['adminLoginUrl'];
        Yii::$app->mchRoleAdmin->logout();
        Yii::$app->response->redirect($url)->send();
    }
}
