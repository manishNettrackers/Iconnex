<?php
$this->breadcrumbs=array(
	'Vehicles'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Vehicle', 'url'=>array('index')),
	array('label'=>'Manage Vehicle', 'url'=>array('admin')),
);
?>

<h1>Maintain Vehicle</h1>

echo get_class($this);

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>