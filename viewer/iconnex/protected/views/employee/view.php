<?php
$this->breadcrumbs=array(
	'Employees'=>array('index'),
	$model->employee_id,
);

$this->menu=array(
	array('label'=>'List Employee', 'url'=>array('index')),
	array('label'=>'Create Employee', 'url'=>array('create')),
	array('label'=>'Update Employee', 'url'=>array('update', 'id'=>$model->employee_id)),
	array('label'=>'Delete Employee', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->employee_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Employee', 'url'=>array('admin')),
);
?>

<h1>View Employee #<?php echo $model->employee_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'operator_id',
		'employee_id',
		'employee_code',
		'fullname',
		'orun_code',
	),
)); ?>
