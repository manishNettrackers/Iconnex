<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('vehicle_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->vehicle_id), array('view', 'id'=>$data->vehicle_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('vehicle_code')); ?>:</b>
	<?php echo CHtml::encode($data->vehicle_code); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('vehicle_type_id')); ?>:</b>
	<?php echo CHtml::encode($data->vehicle_type_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('operator_id')); ?>:</b>
	<?php echo CHtml::encode($data->operator_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('vehicle_reg')); ?>:</b>
	<?php echo CHtml::encode($data->vehicle_reg); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('orun_code')); ?>:</b>
	<?php echo CHtml::encode($data->orun_code); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('vetag_indicator')); ?>:</b>
	<?php echo CHtml::encode($data->vetag_indicator); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('modem_addr')); ?>:</b>
	<?php echo CHtml::encode($data->modem_addr); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_id')); ?>:</b>
	<?php echo CHtml::encode($data->build_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('wheelchair_access')); ?>:</b>
	<?php echo CHtml::encode($data->wheelchair_access); ?>
	<br />

	*/ ?>

</div>