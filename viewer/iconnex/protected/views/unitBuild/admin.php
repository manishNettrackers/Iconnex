<?php
$this->breadcrumbs=array(
	'Unit Builds'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List UnitBuild', 'url'=>array('index')),
	array('label'=>'Create UnitBuild', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('unit-build-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Unit Builds</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'unit-build-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'build_id',
		'operator_id',
		'build_code',
		'unit_type',
		'description',
		'build_parent',
		/*
		'build_status',
		'version_id',
		'build_notes1',
		'build_notes2',
		'build_type',
		'allow_logs',
		'allow_publish',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
