<?php
/**
 * @link
 * @copyright Copyright (c) 2018
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/4/27
 * Time: 14:23
 */


namespace app\modules\api\models\mch;

use app\models\MchCat;
use app\modules\api\models\ApiModel;

class ShopCatForm extends ApiModel
{
    public $mch_id;

    public function rules()
    {
        return [
            ['mch_id', 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $list = MchCat::find()->where([
            'mch_id' => $this->mch_id,
            'parent_id' => 0,
            'is_delete' => 0,
        ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
        foreach ($list as &$item) {
            $sub_list = MchCat::find()->where([
                'mch_id' => $this->mch_id,
                'parent_id' => $item['id'],
                'is_delete' => 0,
            ])->orderBy('sort,addtime DESC')->select('id,name,icon')->asArray()->all();
            $item['list'] = $sub_list;
        }
        return [
            'code' => 0,
            'data' => [
                'list' => $list,
            ],
        ];
    }
}
