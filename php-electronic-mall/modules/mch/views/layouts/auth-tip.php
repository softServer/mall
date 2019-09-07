<?php
$authPass = true;
$inWorkTime = false;
$workStartTime = strtotime(date('Y-m-d') . ' 09:00:00');
$workEndTime = strtotime(date('Y-m-d') . ' 17:30:00');
if (time() >= $workStartTime && time() < $workEndTime) {
    $inWorkTime = true;
}
$isAdmin = false;
if (Yii::$app->controller->is_admin) {
    $isAdmin = true;
}
if ($inWorkTime && $isAdmin) {
    try {
        $hostInfo = \app\hejiang\cloud\Cloud::getHostInfo();
        if (!$hostInfo) {
            throw new Exception();
        }
        if ($hostInfo['code'] !== 0) {
            throw new Exception($hostInfo['msg']);
        }
        $localAuthInfo = \app\hejiang\cloud\Cloud::getLocalAuthInfo();
        if ($localAuthInfo && !empty($localAuthInfo['domain'])) {
            $domain = $localAuthInfo['domain'];
        } else {
            $domain = Yii::$app->request->getHostName();
        }
        if ($domain !== $hostInfo['data']['host']['domain']) {
            throw new Exception();
        }
    } catch (Exception $e) {
        $appendMsg = $e->getMessage();
        $authPass = false;
    }
}
?>
<?php if (!$authPass) : ?>
    <style>
    body > .auth-tip {
        position: fixed;
        z-index: 510;
        top: 15px;
        left: calc(50% - 150px);
        width: 380px;
        padding: 10px;
        border: 1px solid rgba(228, 105, 105, 0.49);
        background: rgba(255, 247, 248, 0.85);
        box-shadow: 0 1px 5px rgba(0, 0, 0, .15);
        color: rgba(0, 0, 0, .5);
        text-align: center;
    }

    body > .auth-tip a {
        color: inherit;
        text-decoration: underline;
    }
    </style>
<?php endif; ?>