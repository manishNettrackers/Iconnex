<?php
$this->breadcrumbs=array(
	'Unit Builds'=>array('index'),
	$model->build_id=>array('view','id'=>$model->build_id),
	'Update',
);

$this->menu=array(
	array('label'=>'List UnitBuild', 'url'=>array('index')),
	array('label'=>'Create UnitBuild', 'url'=>array('create')),
	array('label'=>'View UnitBuild', 'url'=>array('view', 'id'=>$model->build_id)),
	array('label'=>'Manage UnitBuild', 'url'=>array('admin')),
);

?>

<div id="logo-subform">
<h1>Update Build <?php echo $model->build_id; ?></h1>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>

