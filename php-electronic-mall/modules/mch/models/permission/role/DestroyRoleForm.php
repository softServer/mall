<?php
/**
 * link:
 * copyright: Copyright (c) 2018
 * author: wxf
 */

namespace app\modules\mch\models\permission\role;

use app\models\AuthRole;
use app\models\AuthRolePermission;
use app\models\AuthRoleUser;
use app\modules\mch\models\MchModel;
use Yii;

class DestroyRoleForm extends MchModel
{
    public $roleId;

    public function destroy()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $destroyed = AuthRole::findOne($this->roleId)->delete();

        if ($destroyed) {
            $this->destroyRoleUser();
            $this->destroyRolePermission();

            $transaction->commit();

            return [
                'code' => 0,
                'msg' => '删除成功'
            ];
        }

        $transaction->rollBack();
        return $this->getErrorResponse($destroyed);
    }

    public function destroyRoleUser()
    {
        AuthRoleUser::deleteAll(['role_id' => $this->roleId]);
    }


    public function destroyRolePermission()
    {
        AuthRolePermission::deleteAll(['role_id' => $this->roleId]);
    }
}
