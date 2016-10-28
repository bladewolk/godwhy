<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    //BAD CODE START
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script
        src="https://code.jquery.com/jquery-3.1.1.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
        crossorigin="anonymous"></script>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'My Company',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
<script>
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawChart);


    function ajaxLoad(filename) {
        $.ajax({
            url: '<?php echo \yii\helpers\Url::toRoute('site/request'); ?>',
            type: 'post',
            data: {
                'filename': filename,
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>'
            },
            success: function (data) {
                console.log(data.content);
                var data = google.visualization.arrayToDataTable(data.content);
                var options = {
                    title: 'CVS',
                    hAxis: {title: 'Day', titleTextStyle: {color: '#333'}},
                    vAxis: {minValue: 0}
                };


                var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
                chart.draw(data, options);

            }
        });
    }

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Day', 'Download', 'Upload'],
            ['2013', 5, 1],
            ['2014', 6, 2],
            ['2015', 6, 3],
            ['2016', 4, 4]
        ]);
        var options = {
            title: 'CVS',
            hAxis: {title: 'Day', titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0}
        };


//        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
//        chart.draw(data, options);
    }


</script>
</html>
<?php $this->endPage() ?>
