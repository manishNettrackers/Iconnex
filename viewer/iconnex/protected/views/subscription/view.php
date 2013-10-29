<?php
$this->breadcrumbs=array(
	'Subscriptions'=>array('index'),
	$model->subscription_id,
);

$this->menu=array(
	array('label'=>'List Subscription', 'url'=>array('index')),
	array('label'=>'Create Subscription', 'url'=>array('create')),
	array('label'=>'Update Subscription', 'url'=>array('update', 'id'=>$model->subscription_id)),
	array('label'=>'Delete Subscription', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->subscription_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Subscription', 'url'=>array('admin')),
);
?>

<h1>View Subscription #<?php echo $model->subscription_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'subscription_id',
		'subscriber_id',
		'subscription_type',
		'creation_time',
		'start_time',
		'end_time',
		'subscribed_time',
		'update_interval',
		'max_departures',
		'display_thresh',
		'request_id',
		'disabled',
		'subscription_ref',
	),
)); ?>
