<?php
$this->breadcrumbs=array(
	'Unit Builds'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List UnitBuild', 'url'=>array('index')),
	array('label'=>'Manage UnitBuild', 'url'=>array('admin')),
);
?>

<h1>Create UnitBuild</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>