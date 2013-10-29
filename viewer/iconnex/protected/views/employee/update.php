<?php
$this->breadcrumbs=array(
	'Employees'=>array('index'),
	$model->employee_id=>array('view','id'=>$model->employee_id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Employee', 'url'=>array('index')),
	array('label'=>'Create Employee', 'url'=>array('create')),
	array('label'=>'View Employee', 'url'=>array('view', 'id'=>$model->employee_id)),
	array('label'=>'Manage Employee', 'url'=>array('admin')),
);
?>

<h1>Update Employee <?php echo $model->employee_id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>