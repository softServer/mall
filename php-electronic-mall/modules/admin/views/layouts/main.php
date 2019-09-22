<?php
defined('YII_ENV') or exit('Access Denied');

use app\models\Option;
use app\modules\admin\models\AdminMenu;

$version = '1.8.3';
$url_manager = Yii::$app->urlManager;
?>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/admin/js/jquery.min.js?v=<?= $version ?>"></script>
<script>
    $(function() {
        window.location.href = "<?= $url_manager->createUrl(['admin/app/entry', 'id' => 1]) ?>";
    });

</script>