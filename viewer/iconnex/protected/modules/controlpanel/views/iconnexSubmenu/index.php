<?php
$this->breadcrumbs=array(
	'Iconnex Submenus',
);

$this->menu=array(
	array('label'=>'Create IconnexSubmenu', 'url'=>array('create')),
	array('label'=>'Manage IconnexSubmenu', 'url'=>array('admin')),
);
?>

<h1>Iconnex Submenus</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
