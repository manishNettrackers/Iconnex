<?php
$this->breadcrumbs=array(
	'Subscriptions',
);

//$this->menu=array(
//	array('label'=>'Create Subscription', 'url'=>array('create')),
//	array('label'=>'Manage Subscription', 'url'=>array('admin')),
//);
?>

<h1>Subscriptions</h1>

<?php //$this->widget('zii.widgets.CListView', array(
//	'dataProvider'=>$dataProvider,
//	'itemView'=>'_view',
//));
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'subscription-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
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
        array(
            'class'=>'CButtonColumn',
        ),
    ),
)); ?>

