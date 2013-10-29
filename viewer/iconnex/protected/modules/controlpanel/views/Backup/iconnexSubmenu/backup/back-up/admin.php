
<?php  Yii::app()->getComponent('bootstrap');

 $lo_model = iconnexMenu ::model()->findByAttributes(array('menu_id'=>$_REQUEST['id']));
$this->breadcrumbs=array(
	'Controlpanel',
	'Menu'=>array('IconnexMenu/menu'),
	$lo_model->menu_name,
);?>
<div> <?php $this->widget('bootstrap.widgets.TbButton', array(
	'label' => 'Add Submenu',
	'type' => 'primary',
	'url'=>$this->createUrl('IconnexSubmenu/create',array("menu_id"=>$_REQUEST["id"])), // My Remote URL
	
	'htmlOptions'=>array(
										'ajax'=>array(
											'type'=>'POST',
											'cache'=> false,
											'url'=>"js:$(this).attr('href')",
											'success'=>'function(data) { 
												$("#createModal .modal-body p").html(data); 
												$("#createModal").modal(); 
											}',
											

										),
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
					'name' => 'app_name',
					'header' => $lo_model->menu_name,
   				 ),
   array(
			'header' => 'Action',
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'template'=>'{update}&nbsp;{my_delete}',
			
			'buttons'=>array(
				'update'=>array(
									'class'=>'bootstrap.widgets.TbButtonColumn',
									'label'=>'Update',
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/update.png',
									
									'url'=>'Yii::app()->createUrl("controlpanel/IconnexSubmenu/update", array("id"=>$data->app_id,"menu_id"=>$_REQUEST["id"]))',
									'options'=>array(
										'ajax'=>array(
											'type'=>'POST',
											'url'=>"js:$(this).attr('href')",
											'success'=>'function(data) { $("#viewModal .modal-body p").html(data); $("#viewModal").modal(); }'
										),
									),
                    
							 ),
				'my_delete'=>array(
									'class'=>'bootstrap.widgets.TbButtonColumn',
									'label'=>'Delete',
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/delete.png',
									'url'=>'Yii::app()->controller->createUrl(Yii::app()->controller->getId()."/delete")."/id/".$data->app_id."/name/".$data->app_name',
									'click' => "function()
										 {
											var parseUrl = $(this).attr('href').split('/name/');
											if(!confirm('Are you sure you want to delete this submenu  '+parseUrl[1]+'?')) return false;
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
	
	
	
<!-- Update Popup  -->
<?php $this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'viewModal')); ?>
<!-- Popup Header -->
<div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h4>Edit Submenu</h4>
    </div>
<!-- Popup Content -->
<div class="modal-body">
<p>Edit Submenu</p>
</div>
<!-- Popup Footer -->
<div class="modal-footer">

<!-- close button -->
<?php $this->widget('bootstrap.widgets.TbButton', array(
    'label'=>'Close',
    'url'=>'#',
    'htmlOptions'=>array('data-dismiss'=>'modal'),
)); ?>
<!-- close button ends-->
</div>
<?php $this->endWidget(); ?>
<!-- Update Popup ends -->



	<?php
$this->beginWidget('bootstrap.widgets.TbModal', array('id' => 'createModal')); ?>
 
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h4>Add Submenu</h4>
    </div>
 
    <div class="modal-body">
        <p></p>
    </div>
 
    <div class="modal-footer">
		
		<?php $this->widget('bootstrap.widgets.TbButton', array(
		'label' => 'Close',
		'url' => '#',
		'htmlOptions' => array('data-dismiss' => 'modal'),
	)); ?>
    </div>
 
	<?php $this->endWidget(); ?>
	<script language="javascript">
function savedata(url,action)
{
	var formdata=$('#iconnex-submenu-form').serialize();
	 $.ajax({
		 url:"<?php echo Yii::app()->request->baseUrl;?>/index.php?r=/controlpanel/"+url,
		 dataType:'json',
         type:'post',
		 data:formdata,
		 cache: false,
         success:function(data) {
                    if(data.status=="success"){
					
					 if(action=='update')
					 {
					 	$('#viewModal').modal('hide');
						$('#viewModal').remove();
					 }
					 else
					 {
						 $('#createModal').modal('hide');
						 $('#createModal').remove();
					 }
					 $.fn.yiiGridView.update('menu-grid');
                    }
                     else{
                    $.each(data, function(key, val)
					 {
						 $("#IconnexSubmenu_app_name").html(val);
						 alert(val);
                    });
                    }       
                }
		 
		 });
//
}

</script>
	