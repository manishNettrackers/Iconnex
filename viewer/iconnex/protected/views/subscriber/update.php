<?php
$this->breadcrumbs=array(
	'Subscribers'=>array('index'),
	$model->subscriber_id=>array('view','id'=>$model->subscriber_id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Subscriber', 'url'=>array('index')),
	array('label'=>'Create Subscriber', 'url'=>array('create')),
	array('label'=>'View Subscriber', 'url'=>array('view', 'id'=>$model->subscriber_id)),
	array('label'=>'Manage Subscriber', 'url'=>array('admin')),
);
?>

<h1>Update Subscriber <?php echo $model->subscriber_id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>