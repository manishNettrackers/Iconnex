<?php

        ini_set("memory_limit","800M");

$this->breadcrumbs=array(
	'Vehicles'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Vehicle', 'url'=>array('index')),
	array('label'=>'Create Vehicle', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('vehicle-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>


<h1>Manage Vehicles</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'vehicle-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'vehicle_id',
		'vehicle_code',
		'vehicle_type_id',
		'operator_id',
		'vehicle_reg',
		'orun_code',
		/*
		'vetag_indicator',
		'modem_addr',
		'build_id',
		'wheelchair_access',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
