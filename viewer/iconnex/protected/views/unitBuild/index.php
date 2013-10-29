<?php
$this->breadcrumbs=array(
	'Unit Builds',
);

$this->menu=array(
	array('label'=>'Create UnitBuild', 'url'=>array('create')),
	array('label'=>'Manage UnitBuild', 'url'=>array('admin')),
);
?>

<h1>Unit Builds</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
