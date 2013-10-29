<?php Yii::app()->getComponent('bootstrap');
$this->breadcrumbs=array(
'Control Panel',
'Menu',
);


$this->menu=array(
	array('label'=>'List mintu', 'url'=>array('index')),
	array('label'=>'Create mintu', 'url'=>array('create')),
);


?>
<div> <?php $this->widget('bootstrap.widgets.TbButton', array(
	'label' => 'Add Menu',
	'type' => 'primary',
	'htmlOptions' => array(
		'data-toggle' => 'modal',
		'data-target' => '#myModal',
	),
)); ?></div>
<?php

 $this->widget('bootstrap.widgets.TbGridView', array(
    'id' => 'menu-grid',
	'type'=>'striped bordered',
    'itemsCssClass' => 'table-bordered items',
    'dataProvider' => $model->search(),
    'columns'=>array(
			array(
					'class' => 'editable.EditableColumn',
					'name' => 'menu_name',
					//'headerHtmlOptions' => array('style' => 'width: 110px;'),
					'editable' => array( //editable section
											//'apply' => '$data->user_status != 4', //can't edit deleted users
											'url' => $this->createUrl('IconnexMenu/update'),
											'placement' => 'right',
											'validate' => 'js: function(value) {
																					if($.trim(value) == "") return "This field is required";
																				}'
										)
   				 ),
   array(
   			'header' => 'Action',
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'template'=>'{add_subMenu}  {my_delete}',
			
			'buttons'=>array(
				'add_subMenu'=>array(
					'class'=>'bootstrap.widgets.TbButtonColumn',
					'label'=>'Show Submenu',
					'imageUrl'=>Yii::app()->request->baseUrl.'/images/submenu_icon.gif',
					'url'=>'Yii::app()->controller->createUrl("IconnexSubmenu/menu")."&id=".$data->menu_id',
					
					 ),
				'my_delete'=>array(
					'class'=>'cssGridButton',
					'label'=>'Delete Menu',
					'imageUrl'=>Yii::app()->request->baseUrl.'/images/delete.png',
					'url'=>'Yii::app()->controller->createUrl(Yii::app()->controller->getId()."/delete")."/id/".$data->menu_id."/name/".$data->menu_name',
					'click' => "function()
						 {
							var parseUrl = $(this).attr('href').split('/name/');
							if(!confirm('Are you sure you want to delete this menu  '+parseUrl[1]+'?')) return false;
							$.fn.yiiGridView.update('menu-grid', {
							type:'POST',
							url:$(this).attr('href'),
							success:function(data) {
							$('.flash-success').css('display','block').html(data);
							$.fn.yiiGridView.update('menu-grid');
							}
							});
							return false;
						 }",
					 ),
			),
			
			
		),
		
		
		
		
		
    ),
    ));
    ?>
	
	
	<?php
$this->beginWidget('bootstrap.widgets.TbModal', array('id' => 'myModal')); ?>
 
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h4>Add Menu</h4>
    </div>
 
    <div class="modal-body">
        <p><input type="text" name="addmenu" value="" id="addmenu"></p>
    </div>
 
    <div class="modal-footer">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
		'type' => 'primary',
		'label' => 'Save changes',
		'url' => '#',
		'htmlOptions' => array('data-dismiss' => 'modal','onclick'=>'addmenu()'),
	)); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array(
		'label' => 'Close',
		'url' => '#',
		'htmlOptions' => array('data-dismiss' => 'modal'),
	)); ?>
    </div>
 
	<?php $this->endWidget(); ?>
	
	<script language="javascript">
	function addmenu()
	{
		var menu=$('#addmenu').val();
		if(menu !='')
		{
		 $.ajax({
				type : 'POST',
				url: "<?php echo Yii::app()->request->baseUrl;?>/index.php?r=/controlpanel/IconnexMenu/create",
				datatype: "json",
				data: { 'menu': menu},
				success: function(data){
					$.fn.yiiGridView.update('menu-grid');
					}
				})
		}
	}
	</script>