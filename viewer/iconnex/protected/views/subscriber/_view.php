<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscriber_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->subscriber_id), array('view', 'id'=>$data->subscriber_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscriber_code')); ?>:</b>
	<?php echo CHtml::encode($data->subscriber_code); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('user_id')); ?>:</b>
	<?php echo CHtml::encode($data->user_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('ip_address')); ?>:</b>
	<?php echo CHtml::encode($data->ip_address); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('gateway_id')); ?>:</b>
	<?php echo CHtml::encode($data->gateway_id); ?>
	<br />


</div>