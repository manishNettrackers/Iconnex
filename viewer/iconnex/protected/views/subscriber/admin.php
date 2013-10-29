<?php
$this->breadcrumbs=array(
	'Subscribers',
);

$this->menu=array(
	array('label'=>'Create Subscriber', 'url'=>array('create')),
	array('label'=>'User Permissions', 'url'=>array('userLoc/admin')),
);
?>

<h1>SIRI Subscriber Management </h1>

<p>
You can create a new subscriber by selecting "Create Subscriber" from the menu on the right.
Use the buttons in the "Actions" column in the table below to perform actions on existing subscribers.
<ul>
    <li>The View button shows the subscriber's full entry in the subscriber table.</li>
    <li>Use the Edit button to modify the subscriber's entry in the subscriber table<.li>
    <li>Use the Delete button to delete the subscriber completeley. CAUTION! This will remove all configured subscripitions for that subscriber!</li>
    <li>The Subscriptions button will take you to a screen where you can configure subscription locations and parameters for the subscriber.</li>
</ul
</p>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'subscription-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
//        'subscriber_id',
        'subscriber_code',
        array('name'=>'usernm', 'value'=>'$data->user->usernm'),
//        '$data->user->usernm:text:User',
        'ip_address',
        'gateway_id',
        array(
            'class'=>'CButtonColumn',
            'template'=>'{view} {update} {delete} {subscriptions} {permissions}',
            'header'=>'Actions',
            'buttons'=>array(
                'subscriptions' => array(
                    'label'=>'Subscriptions',
                    'url'=>'Yii::app()->createUrl("subscription/admin", array("subscriber_id"=>$data->subscriber_id))',
                    'imageUrl'=>Yii::app()->request->baseUrl.'/images/subscriptions.png',
                ),
                'permissions' => array(
                    'label'=>'Permissions',
                    'url'=>'Yii::app()->createUrl("userLoc/admin", array("subscriber_id"=>$data->subscriber_id))',
                    'imageUrl'=>Yii::app()->request->baseUrl.'/images/permissions.png',
                ),
            ),
            'htmlOptions'=>array('width'=>100),
        ),
    ),
)); ?>

