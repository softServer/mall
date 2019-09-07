<?php
/**
 * @link:
 * @copyright: Copyright (c) 2018
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/11/19
 * Time: 11:44
 */

namespace app\models\task\order;


use app\hejiang\task\TaskRunnable;
use app\models\ActionLog;
use app\models\IntegralOrder;
use app\models\Mch;
use app\models\MchAccountLog;
use app\models\MchPlugin;
use app\models\MchSetting;
use app\models\MsOrder;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderShare;
use app\models\PtOrder;
use app\models\Register;
use app\models\Setting;
use app\models\Store;
use app\models\User;
use app\models\UserShareMoney;

/**
 * @property Store $store
 */
class OrderAuthSale extends TaskRunnable
{
    const STORE = 'STORE';
    const MS = 'MIAOSHA';
    const PT = 'PINTUAN';
    const INTEGRAL = 'INTEGRAL';

    public $store;
    public $actionType;
    public $params = [];
    /* @var $order Order|PtOrder|MsOrder|IntegralOrder */
    public $order;

    public function run($param = [])
    {
        $this->store = Store::findOne($param['store_id']);
        $this->params = $param;

        switch($param['order_type']) {
            case self::STORE:
                $this->order = Order::find()->where([
                    'id' => $this->params['order_id'],
                    'is_pay' => Order::IS_PAY_TRUE,
                    'is_cancel' => Order::IS_CANCEL_FALSE,
                    'is_delete' => Order::IS_DELETE_FALSE,
                    'is_send' => Order::IS_SEND_TRUE,
                    'is_confirm' => Order::IS_CONFIRM_TRUE
                ])->with('refund')->one();
                if(!$this->order) {
                    $this->addTask(['message' => '订单不满足状态']);
                    return false;
                }
                switch($this->order->type) {
                    case 0:
                        $this->actionType = "商城订单过售后";
                        break;
                    case 1:
                        $this->actionType = "砍价订单过售后";
                        break;
                    case 2:
                        $this->actionType = "九宫格订单过售后";
                        break;
                    case 3:
                        $this->actionType = "刮刮卡订单过售后";
                        break;
                    case 4:
                        $this->actionType = "抽奖订单过售后";
                        break;
                }
                break;
            case self::MS:
                $this->actionType = "秒杀订单过售后";
                $this->order = MsOrder::find()->where([
                    'id' => $this->params['order_id'],
                    'is_pay' => Order::IS_PAY_TRUE,
                    'is_cancel' => Order::IS_CANCEL_FALSE,
                    'is_delete' => Order::IS_DELETE_FALSE,
                    'is_send' => Order::IS_SEND_TRUE,
                    'is_confirm' => Order::IS_CONFIRM_TRUE
                ])->with('refund')->one();
                break;
            case self::PT:
                $this->actionType = '拼团订单过售后';
                $this->order = PtOrder::find()->where([
                    'id' => $this->params['order_id'],
                    'is_pay' => Order::IS_PAY_TRUE,
                    'is_cancel' => Order::IS_CANCEL_FALSE,
                    'is_delete' => Order::IS_DELETE_FALSE,
                    'is_send' => Order::IS_SEND_TRUE,
                    'is_confirm' => Order::IS_CONFIRM_TRUE,
                ])->with('refund')->one();
                break;
            default:
                return true;
        }
        if(!$this->order) {
            $this->addTask(['message' => '订单不满足状态']);
            return false;
        }
        if($this->order->refund) {
            if($this->order->refund->type == 1) {
                if($this->order->refund->status == 1) {
                    $this->addTask(['message' => '订单已退款']);
                    return true;
                } else if ($this->order->refund->status == 0) {
                    $this->addTask(['message' => '订单处于退款退货流程']);
                    return false;
                }
            }
        }
        if($this->order->is_price == 1) {
            $this->addTask(['message' => '订单已售后']);
            return true;
        }
        return $this->exe();
    }

    // 保存错误信息到日志
    private function addTask($params = [])
    {
        ActionLog::addTask($params, $this->order->store_id, $this->actionType, $this->order->id);
    }

    private function exe()
    {
        $t = \Yii::$app->db->beginTransaction();
        try{
            switch($this->params['order_type']) {
                case self::STORE:
                    $this->order->is_sale = 1;
                    break;
                case self::MS:
                    $this->order->is_sale = 1;
                    break;
                case self::PT:
                    $this->order->is_price = 1;
                    break;
                default:
                    return true;
            }
            if($this->order->save()) {
                $this->share_money();
                $this->give_integral();
                //入驻商户订单金额转到商户余额，请在判断售后的方法之后调用
                $this->transferToMch();
                $t->commit();
                return true;
            } else {
                $t->rollBack();
                $this->addTask($this->order->errors);
                return false;
            }
        } catch(\Exception $e) {
            $t->rollBack();
            $errorInfo = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ];
            $this->addTask($errorInfo);
            return false;
        }
    }

    // 发放佣金
    private function share_money()
    {
        if($this->order->mch_id && $this->order->mch_id > 0) {
            $mchPlugin = MchPlugin::findOne(['mch_id' => $this->order->mch_id, 'store_id' => $this->store->id]);
            if (!$mchPlugin || $mchPlugin->is_share == 0) {
                $this->addTask(['message' => '多商户未分配分销权限']);
                return true;
            }
            $mchSetting = MchSetting::findOne(['mch_id' => $this->order->mch_id, 'store_id' => $this->store->id]);
            if (!$mchSetting || $mchSetting->is_share == 0) {
                $this->addTask(['message' => '商户未设置分销']);
                return true;
            }
        }
        $shareSetting = Setting::findOne(['store_id' => $this->store->id]);
        if ($shareSetting->level == 0) {
            $this->addTask(['message' => '平台未设置分销']);
            return true;
        }
        if($this->params['order_type'] == self::STORE) {
            if ($this->order->is_price != 0) {
                $this->addTask(['message' => '订单已发放佣金']);
                return true;
            }
        }
        $this->order->is_price = 1;

        // 获取分销信息
        if($this->params['order_type'] == self::PT) {
            $orderShare = OrderShare::findOne([
                'store_id' => $this->order->store_id, 'type' => 0,'order_id' => $this->order->id
            ]);
            $rebate = $orderShare->rebate;
            $shareMoney1 = $orderShare->first_price;
            $shareMoney2 = $orderShare->second_price;
            $shareMoney3 = $orderShare->third_price;
            $userId = $orderShare->user_id;
            $parentId1 = $orderShare->parent_id_1;
            $parentId2 = $orderShare->parent_id_2;
            $parentId3 = $orderShare->parent_id_3;
        } else {
            $rebate = $this->order->rebate;
            $shareMoney1 = $this->order->first_price;
            $shareMoney2 = $this->order->second_price;
            $shareMoney3 = $this->order->third_price;
            $userId = $this->order->user_id;
            $parentId1 = $this->order->parent_id;
            $parentId2 = $this->order->parent_id_1;
            $parentId3 = $this->order->parent_id_2;
        }

        $this->order->share_price = 0;
        $user = User::findOne($userId);
        if($rebate > 0) {
            $this->set_money($rebate, $userId, 4);
        }

        if($shareSetting->level == 4) {
            $this->addTask(['message' => '开启分销自购,level=4']);
            return true;
        }
        if(($shareSetting->level > 1 || ($shareSetting->level == 1 && ($shareSetting->is_rebate == 0 || $user->is_distributor == 0))) && $parentId1 > 0) {
            $this->set_money($shareMoney1, $parentId1, 1);
        }
        if(($shareSetting->level > 2 || ($shareSetting->level == 2 && ($shareSetting->is_rebate == 0 || $user->is_distributor == 0))) && $parentId2 > 0) {
            $this->set_money($shareMoney2, $parentId2, 2);
        }
        if($shareSetting->level >= 3 && ($shareSetting->is_rebate == 0 || $user->is_distributor == 0) && $parentId3 > 0) {
            $this->set_money($shareMoney3, $parentId3, 3);
        }
        $this->order->save();
    }

    /**
     * @param $money double 佣金
     * @param $userId integer 获得该笔佣金的用户ID
     * @param $source integer 佣金来源 1--一级分销 2--二级分销 3--三级分销 4--自购返利
     * @return bool
     */
    private function set_money($money, $userId, $source)
    {
        $array = [
            self::STORE => 0,
            self::MS => 1,
            self::PT => 2,
        ];
        $orderType = $array[$this->params['order_type']];
        if($this->params['order_type'] == 'STORE') {
            $this->order->share_price += $money;
        }

        $user = User::findOne($userId);
        if(!$user) {
            $this->addTask(['message' => "分销商不存在，user_id={$userId}"]);
            return false;
        }
        $user->total_price += doubleval($money);
        $user->price += doubleval($money);
        $user->save();
        UserShareMoney::set($money, $userId, $this->order->id, 0, $source, $this->order->store_id, $orderType);
        return true;
    }

    // 发放积分
    private function give_integral()
    {
        if(!in_array($this->params['order_type'], ['STORE', 'MS'])) {
            return false;
        }
        if(!isset($this->order->give_integral)) {
            return false;
        }
        if($this->order->give_integral != 0) {
            $this->addTask(['message' => '已发放积分']);
            return true;
        }
        $integral = 0;
        if($this->params['order_type'] == 0) {
            $integral = OrderDetail::find()
                ->andWhere(['order_id' => $this->order->id, 'is_delete' => 0])
                ->select([
                    'sum(integral)',
                ])->scalar();
        }
        if($this->params['order_type'] == 1) {
            $integral = $this->order->integral_amount;
        }
        $giveUser = User::findOne(['id' => $this->order->user_id]);
        $giveUser->integral += $integral;
        $giveUser->total_integral += $integral;
        $giveUser->save();
        $this->order->give_integral = 1;
        if(!$this->order->save()) {
            $this->addTask($this->order->errors);
            return false;
        }
        $register = new Register();
        $register->store_id = $this->order->store_id;
        $register->user_id = $this->order->user_id;
        $register->register_time = '..';
        $register->addtime = time();
        $register->continuation = 0;
        $register->type = 8;
        $register->integral = $integral;
        $register->order_id = $this->order->id;
        if(!$register->save()) {
            $this->addTask($register->errors);
            return false;
        }
    }

    // 多商户金额结算
    private function transferToMch()
    {
        if($this->params['order_type'] != 'STORE') {
            $this->addTask(['message' => '不是商城订单']);
            return false;
        }
        if($this->order->mch_id == 0) {
            $this->addTask(['message' => '不是多商户订单']);
            return false;
        }
        if($this->order->is_transfer != 0) {
            $this->addTask(['message' => '订单已结算']);
            return false;
        }
        $mch = Mch::findOne($this->order->mch_id);
        if(!$mch) {
            $this->addTask(['message' => '商户不存在']);
            return false;
        }
        $accountMoney = floatval($this->order->pay_price * (1 - floatval($mch->transfer_rate) / 1000)) - $this->order->share_price;
        $mch->account_money = floatval($mch->account_money) + $accountMoney;
        $mch->save();
        $this->order->is_transfer = 1;
        if (!$this->order->save()) {
            \Yii::warning($this->order->errors);
            $this->addTask($this->order->errors);
        }

        $log = new MchAccountLog();
        $log->store_id = $this->order->store_id;
        $log->mch_id = $mch->id;
        $log->type = 1;
        $log->price = $accountMoney;
        $log->desc = '订单（' . $this->order->order_no . '）结算';
        $log->addtime = time();
        if (!$log->save()) {
            \Yii::warning($log->errors);
            $this->addTask($log->errors);
            return false;
        }
    }
}