<?php
$this->breadcrumbs=array(
	'Unit Builds'=>array('index'),
	$model->build_id,
);

$this->menu=array(
	array('label'=>'List UnitBuild', 'url'=>array('index')),
	array('label'=>'Create UnitBuild', 'url'=>array('create')),
	array('label'=>'Update UnitBuild', 'url'=>array('update', 'id'=>$model->build_id)),
	array('label'=>'Delete UnitBuild', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->build_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage UnitBuild', 'url'=>array('admin')),
);
?>

<h1>View UnitBuild #<?php echo $model->build_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'build_id',
		'operator_id',
		'build_code',
		'unit_type',
		'description',
		'build_parent',
		'build_status',
		'version_id',
		'build_notes1',
		'build_notes2',
		'build_type',
		'allow_logs',
		'allow_publish',
	),
)); ?>
