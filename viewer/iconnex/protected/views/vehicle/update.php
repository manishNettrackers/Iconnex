<?php
$this->breadcrumbs=array(
	'Vehicles'=>array('index'),
	$model->vehicle_id=>array('view','id'=>$model->vehicle_id),
	'Update',
);

ini_set("memory_limit","800M");

$this->menu=array(
	array('label'=>'List Vehicle', 'url'=>array('index')),
	array('label'=>'Create Vehicle', 'url'=>array('create')),
	array('label'=>'View Vehicle', 'url'=>array('view', 'id'=>$model->vehicle_id)),
	array('label'=>'Manage Vehicle', 'url'=>array('admin')),
);
?>

<div id="logo-subform">
<h1>Update Vehicle <?php echo $model->vehicle_id; ?></h1>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>
