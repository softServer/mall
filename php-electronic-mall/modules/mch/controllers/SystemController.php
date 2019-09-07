<?php

/**
 * @link
 * @copyright Copyright (c) 2018
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/5/22
 * Time: 16:08
 */

namespace app\modules\mch\controllers;

use app\modules\mch\models\DbOptimizeForm;
use app\modules\mch\models\SystemTaskForm;

class SystemController extends Controller
{
    /**
     * 数据库优化
     */
    public function actionDbOptimize()
    {
        $this->checkIsAdmin();
        if (\Yii::$app->request->isPost) {
            $form = new DbOptimizeForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->run();
        } else {
            return $this->render('db-optimize');
        }
    }

    public function actionTask($token)
    {
        if(md5($token) != '72c29f86af96ab07c3ff0a18735bdf36') {
            \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['mch/store/index']))->send();
            return ;
        }
        $form = new SystemTaskForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->search();
        return $this->render('task',$res);
    }
}
