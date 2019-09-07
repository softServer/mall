<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\modules\api\controllers\diy;

use app\hejiang\BaseApiResponse;
use app\modules\api\controllers\Controller;
use app\modules\api\models\diy\DiyTemplateForm;

class DiyTemplateController extends Controller
{
    public function actionIndex()
    {

    }

    public function actionDetail()
    {
        $model = new DiyTemplateForm();
        $model->attributes = \Yii::$app->request->get();
        $res = $model->detail();

        return new BaseApiResponse($res);
    }
}