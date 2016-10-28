<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'file')->fileInput() ?>

<div class="form-group">
    <?= Html::submitButton('Scan', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php foreach ($files as $item) {
    echo '<li onclick=ajaxLoad("' . $item . '") ><b > ';
    echo $item;
    echo '</b ></li > ';
}
?>

<div class="files">

</div>

<div id="chart_div" style="width: 100%; height: 500px;"></div>