<?php
$this->breadcrumbs=array(
	'Iconnex Menus',
);

$this->menu=array(
	//array('label'=>'List iconnexMenu', 'url'=>array('index')),
	array('label'=>Yii::t('b.iconnexMenu','Create iconnexMenu'), 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('iconnex-menu-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Iconnex Menus</h1>
<div class="flash-success" style="display:<?php echo (Yii::app()->user->hasFlash("iconnexMenu")?"":"none") ?>;">
	<?php echo Yii::app()->user->getFlash('iconnexMenu'); ?></div>
<!--<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>-->

<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'iconnex-menu-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'emptyText'=>Yii::t('b.iconnexMenu','No iconnexMenu found'),
	'columns'=>array(
		'menu_id',
		'menu_name',
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update}&nbsp;{my_delete}',
			'buttons'=>array(
				'update'=>array(
									'class'=>'cssGridButton',
									'label'=>Yii::t('b.iconnexMenu','Update'),
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/update.png'
								),	
				'my_delete'=>array(
					'class'=>'cssGridButton',
					'label'=>Yii::t('b.iconnexMenu','Delete'),
					'visible' => '!Yii::app()->user->hasRole("Settings Editor")',
					'imageUrl'=>Yii::app()->request->baseUrl.'/images/delete.png',
					'url'=>'Yii::app()->controller->createUrl(Yii::app()->controller->getId()."/delete")."/".$data->id."?name/".$data->settings_key',
					'click' => "function()
						 {
							var parseUrl = $(this).attr('href').split('?name/');
							if(!confirm('".Yii::t('b.iconnexMenu','Are you sure you want to delete this iconnexMenu')." '+parseUrl[1]+'?')) return false;
							$.fn.yiiGridView.update('iconnex-menu-grid', {
							type:'POST',
							url:$(this).attr('href'),
							success:function(data) {
							$('.flash-success').css('display','block').html(data);
							$.fn.yiiGridView.update('iconnex-menu-grid');
							}
							});
							return false;
						 }",
					 ),
			),
		),
	),
)); ?>