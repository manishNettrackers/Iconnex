<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscription_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->subscription_id), array('view', 'id'=>$data->subscription_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscriber_id')); ?>:</b>
	<?php echo CHtml::encode($data->subscriber_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscription_type')); ?>:</b>
	<?php echo CHtml::encode($data->subscription_type); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('creation_time')); ?>:</b>
	<?php echo CHtml::encode($data->creation_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('start_time')); ?>:</b>
	<?php echo CHtml::encode($data->start_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('end_time')); ?>:</b>
	<?php echo CHtml::encode($data->end_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscribed_time')); ?>:</b>
	<?php echo CHtml::encode($data->subscribed_time); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('update_interval')); ?>:</b>
	<?php echo CHtml::encode($data->update_interval); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('max_departures')); ?>:</b>
	<?php echo CHtml::encode($data->max_departures); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('display_thresh')); ?>:</b>
	<?php echo CHtml::encode($data->display_thresh); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('request_id')); ?>:</b>
	<?php echo CHtml::encode($data->request_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('disabled')); ?>:</b>
	<?php echo CHtml::encode($data->disabled); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subscription_ref')); ?>:</b>
	<?php echo CHtml::encode($data->subscription_ref); ?>
	<br />

	*/ ?>

</div>