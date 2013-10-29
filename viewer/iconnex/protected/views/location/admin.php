<?php

        ini_set("memory_limit","800M");

$this->breadcrumbs=array(
	'Locations'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Location', 'url'=>array('index')),
	array('label'=>'Create Location', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('location-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Locations</h1>

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
	'id'=>'location-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'location_id',
		'location_code',
		'gprs_xmit_code',
		'point_type',
		'route_area_id',
		'description',
		/*
		'public_name',
		'receive',
		'latitude_degrees',
		'latitude_minutes',
		'latitude_heading',
		'longitude_degrees',
		'longitude_minutes',
		'longitude_heading',
		'geofence_radius',
		'pass_angle',
		'gazetteer_code',
		'gazetteer_id',
		'place_id',
		'district_id',
		'arriving_addon',
		'exit_addon',
		'bay_no',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
