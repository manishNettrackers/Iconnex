<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->build_id), array('view', 'id'=>$data->build_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('operator_id')); ?>:</b>
	<?php echo CHtml::encode($data->operator_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_code')); ?>:</b>
	<?php echo CHtml::encode($data->build_code); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('unit_type')); ?>:</b>
	<?php echo CHtml::encode($data->unit_type); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_parent')); ?>:</b>
	<?php echo CHtml::encode($data->build_parent); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_status')); ?>:</b>
	<?php echo CHtml::encode($data->build_status); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('version_id')); ?>:</b>
	<?php echo CHtml::encode($data->version_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_notes1')); ?>:</b>
	<?php echo CHtml::encode($data->build_notes1); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_notes2')); ?>:</b>
	<?php echo CHtml::encode($data->build_notes2); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('build_type')); ?>:</b>
	<?php echo CHtml::encode($data->build_type); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('allow_logs')); ?>:</b>
	<?php echo CHtml::encode($data->allow_logs); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('allow_publish')); ?>:</b>
	<?php echo CHtml::encode($data->allow_publish); ?>
	<br />

	*/ ?>

</div>