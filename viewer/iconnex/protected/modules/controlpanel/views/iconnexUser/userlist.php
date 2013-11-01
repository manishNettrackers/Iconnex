<div class="container1">
<div class="siglepage">

<?php  Yii::app()->getComponent('bootstrap');
$this->breadcrumbs=array(
	'Control Panel',
	'User'=>array('IconnexUser/user'),

);?>

<div> <?php $this->widget('bootstrap.widgets.TbButton', array(
	'label' => 'Add User',
	'type' => 'primary',
	'url'=>$this->createUrl('IconnexUser/create'), // My Remote URL
	
	'htmlOptions'=>array(
							'ajax'=>array(
								'type'=>'POST',
								'cache'=> false,
								'url'=>"js:$(this).attr('href')",
								'success'=>'function(data) { 
									$("#createModal .modal-body p").html(data); 
									$("#createModal .modal-header h4").html("Create User");
									$("#createModal").modal(); 
								}',
								

							),
						),
)); ?></div>
<?php
	$this->widget('bootstrap.widgets.TbGridView', array(
    'id' => 'iconnex-user-grid',
	'type'=>'striped bordered',
    'itemsCssClass' => 'table-bordered items',
    'dataProvider' => $model->search(),
    'columns'=>array(
			array(
					'name' => 'usernm',
					'header' =>'User Name',
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
									'url'=>'Yii::app()->createUrl("controlpanel/IconnexUser/update", array("id"=>$data->userid))',
									'click' => "function()
										 {
											$.fn.yiiGridView.update('iconnex-user-grid', {
											type:'POST',
											url:$(this).attr('href'),
											success:function(data) {
											$.fn.yiiGridView.update('iconnex-user-grid');	
											$('#createModal .modal-body p').html(data); 
												$('#createModal .modal-header h4').html('Update User');
												$('#createModal').modal();
											}
											});
											return false;
										 }",
					  			 ),
			
				'my_delete'=>array(
									'class'=>'bootstrap.widgets.TbButtonColumn',
									'label'=>'Delete',
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/delete.png',
									'url'=>'Yii::app()->controller->createUrl(Yii::app()->controller->getId()."/delete")."/id/".$data->userid."/name/".$data->usernm',
									'click' => "function()
										 {
											 
											var parseUrl = $(this).attr('href').split('/name/');
											if(!confirm('Are you sure you want to delete this user  '+parseUrl[1]+'?')) return false;
											$.fn.yiiGridView.update('iconnex-user-grid', {
											type:'POST',
											url:$(this).attr('href'),
											success:function(data) {
											$('.flash-success').css('display','block').html(data);
											$.fn.yiiGridView.update('iconnex-user-grid');
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
	$this->beginWidget('bootstrap.widgets.TbModal', array('id' => 'createModal')); ?>
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h4></h4>
    </div>
    <div class="modal-body">
        <p></p>
    </div>
    <div class="modal-footer">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
		'type' => 'primary',
		'label' => 'Save',
		//'url' => '#',
		'htmlOptions' => array('onclick'=>'savedata()'),
	)); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array(
		'label' => 'Close',
		'url' => '',
		'htmlOptions' => array('data-dismiss' => 'modal','onclick'=>'cleardata();'),
		)); ?>
    </div>
	<?php $this->endWidget(); ?>
	
<script language="javascript">

	function cleardata(model)
	{
		
		$("#createModal .modal-body p").html(''); 
		$('#createModal').modal('hide');
		//$.fn.yiiGridView.update("iconnex-user-grid");
	}
function savedata()
{
	var url=$('#url').val();
	var action=$('#action').val();
	$("#IconnexUser_usernm_"+action).html('');
	$("#IconnexUser_passwd_md5_"+action).html('');
	$("#IconnexUser_emailad_"+action).html('');
	var formdata=$('#iconnex-user-form').serialize();
	 $.ajax({
		 url:"<?php echo Yii::app()->request->baseUrl;?>/index.php?r=/controlpanel/"+url,
		 dataType:'json',
         type:'post',
		 data:formdata,
		 cache: false,
         success:function(data) {
                    if(data.status=="success")
					{
					     $("#createModal .modal-body p").html(''); 
						 $('#createModal').modal('hide');
						// $('#createModal').remove();
					 	 //$.fn.yiiGridView.update('iconnex-user-grid');
						 $.fn.yiiGridView.update("iconnex-user-grid");
                    }
                     else
					 {
						if(data.IconnexUser_usernm)
						$("#IconnexUser_usernm_"+action).html(data.IconnexUser_usernm);
						if(data.IconnexUser_passwd_md5)
						$("#IconnexUser_passwd_md5_"+action).html(data.IconnexUser_passwd_md5);
						if(data.IconnexUser_emailad)
						$("#IconnexUser_emailad_"+action).html(data.IconnexUser_emailad);		
                    }       
                }
		 
		 });
//
}

</script>
	</div>
    </div>