<?php
$this->breadcrumbs=array(
	'Locations'=>array('index'),
	$model->location_id,
);

$this->menu=array(
	array('label'=>'List Location', 'url'=>array('index')),
	array('label'=>'Create Location', 'url'=>array('create')),
	array('label'=>'Update Location', 'url'=>array('update', 'id'=>$model->location_id)),
	array('label'=>'Delete Location', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->location_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Location', 'url'=>array('admin')),
);
?>

<h1>View Location #<?php echo $model->location_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'location_id',
		'location_code',
		'gprs_xmit_code',
		'point_type',
		'route_area_id',
		'description',
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
	),
)); ?>
