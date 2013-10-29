<?php
$this->breadcrumbs=array(
	'Subscribers'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Subscriber', 'url'=>array('index')),
	array('label'=>'Manage Subscriber', 'url'=>array('admin')),
);
?>

<h1>Create Subscriber</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>