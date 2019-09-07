<?php
/**
 * @link
 * @copyright Copyright (c) 2018
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/4/28
 * Time: 16:12
 */


namespace app\modules\api\models\mch;


use app\models\Mch;
use app\modules\api\models\Model;

class CashForm extends Model
{
    public $mch_id;
    public $cash_val;

    public function rules()
    {
        return [
            [['cash_val'], 'required'],
            [['cash_val'], 'number', 'min' => 0.01,],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cash_val' => '提现金额',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $mch = Mch::findOne($this->mch_id);
        if (!$mch)
            return [
                'code' => 1,
                'msg' => '商户不存在。',
            ];
    }
}