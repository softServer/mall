<?php
/**
 * @link:
 * @copyright: Copyright (c) 2018
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/10/11
 * Time: 11:50
 */

namespace app\modules\mch\models\diy;


use app\models\Goods;
use app\models\GoodsCat;
use app\models\MiaoshaGoods;
use app\models\MsGoods;
use app\modules\mch\models\MchModel;
use app\utils\DiyGoods;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class GoodsForm extends MchModel
{
    public $type;

    public $cat;
    public $page;
    public $limit = 8;
    public $idList;
    public $is_page = true;
    public $mch;

    public function search()
    {
        $res = DiyGoods::getGoods($this->type, $this->idList, $this->cat, $this->limit,$this->is_page, $this->page, $this->mch);

        /* @var Pagination $pagination */
        $pagination = $res['pagination'];
        $res['count'] = $pagination->totalCount;
        $res['page'] = $pagination->page + 1;
        $res['page_count'] = $pagination->pageCount;
        $res['page_url'] = \Yii::$app->urlManager->createUrl(['mch/diy/diy/get-goods']);
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $res
        ];
    }
}