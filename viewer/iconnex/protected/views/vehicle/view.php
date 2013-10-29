<?php
$this->breadcrumbs=array(
	'Vehicles'=>array('index'),
	$model->vehicle_id,
);

$this->menu=array(
	array('label'=>'List Vehicle', 'url'=>array('index')),
	array('label'=>'Create Vehicle', 'url'=>array('create')),
	array('label'=>'Update Vehicle', 'url'=>array('update', 'id'=>$model->vehicle_id)),
	array('label'=>'Delete Vehicle', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->vehicle_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Vehicle', 'url'=>array('admin')),
);
?>

<h1>View Vehicle #<?php echo $model->vehicle_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'vehicle_id',
		'vehicle_code',
		'vehicle_type_id',
		'operator_id',
		'vehicle_reg',
		'orun_code',
		'vetag_indicator',
		'modem_addr',
		'build_id',
		'wheelchair_access',
	),
)); ?>
