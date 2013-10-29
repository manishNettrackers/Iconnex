<?php
$this->breadcrumbs=array(
	'Subscribers'=>array('index'),
	$model->subscriber_id,
);

$this->menu=array(
	array('label'=>'List Subscriber', 'url'=>array('index')),
	array('label'=>'Create Subscriber', 'url'=>array('create')),
	array('label'=>'Update Subscriber', 'url'=>array('update', 'id'=>$model->subscriber_id)),
	array('label'=>'Delete Subscriber', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->subscriber_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Subscriber', 'url'=>array('admin')),
);
?>

<h1>View Subscriber #<?php echo $model->subscriber_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'subscriber_id',
		'subscriber_code',
		'user_id',
		'ip_address',
		'gateway_id',
	),
)); ?>
