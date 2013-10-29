<?php Yii::app()->getComponent('bootstrap');
$this->breadcrumbs=array(
	'Control Panel',
	'Bus Driver'=>array('Employee/busdriver'),
);
 ini_set("memory_limit","800M");

?>
<div> <?php $this->widget('bootstrap.widgets.TbButton', array(
	'label' => 'Add Bus Driver',
	'type' => 'primary',
	'url'=>$this->createUrl('Employee/create'), // My Remote URL
	
	'htmlOptions'=>array(
										'ajax'=>array(
											'type'=>'POST',
											'cache'=> false,
											'url'=>"js:$(this).attr('href')",
											'success'=>'function(data) { 
												$("#createModal .modal-body p").html(data); 
												$("#createModal .modal-header h4").html("Create Bus Driver");
												$("#createModal").modal(); 
											}',
											

										),
									),
)); ?></div>
<?php
	$this->widget('bootstrap.widgets.TbGridView', array(
    'id' => 'employee-grid',
	'type'=>'striped bordered',
    'itemsCssClass' => 'table-bordered items',
    'dataProvider' => $model->search(),
	'filter'=> $model,
    'columns'=>array(
			array(
					'name' => 'fullname',
					'header' => 'Bus Driver Name ',
   				 ),
				 
			array(
					'name' => 'employee_code',
					'header' => 'Employee Code',
   				 ),	 
				 

			 
   array(
			'header' => 'Action',
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'template'=>'{update}&nbsp;{my_delete}',
			
			'buttons'=>array(
				
				'update'=>array(
									'class'=>'bootstrap.widgets.TbButtonColumn',
									'label'=>'Update Bus Driver',
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/update.png',
									'url'=>'Yii::app()->createUrl("controlpanel/Employee/update", array("id"=>$data->employee_id))',
									'click' => "function()
										 {
											$.fn.yiiGridView.update('employee-grid', {
											type:'POST',
											url:$(this).attr('href'),
											success:function(data) {
											$.fn.yiiGridView.update('employee-grid');	
											$('#createModal .modal-body p').html(data); 
												$('#createModal .modal-header h4').html('Update Bus Driver');
												$('#createModal').modal();
											}
											});
											return false;
										 }",
					  			 ),
				'my_delete'=>array(
									'class'=>'bootstrap.widgets.TbButtonColumn',
									'label'=>'Delete Bus Driver',
									'imageUrl'=>Yii::app()->request->baseUrl.'/images/delete.png',
									'url'=>'Yii::app()->controller->createUrl(Yii::app()->controller->getId()."/delete")."/id/".$data->employee_id."/name/".$data->fullname',
									'click' => "function()
										 {
											var parseUrl = $(this).attr('href').split('/name/');
											if(!confirm('Are you sure you want to delete this Bus Driver  '+parseUrl[1]+'?')) return false;
											$.fn.yiiGridView.update('employee-grid', {
											type:'POST',
											url:$(this).attr('href'),
											success:function(data) {
											$('.flash-success').css('display','block').html(data);
											$.fn.yiiGridView.update('employee-grid');
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
	}
function savedata()
{
	var url=$('#url').val();
	var action=$('#action').val();
	document.getElementById("Employee_fullname_"+action).innerHTML='';
	document.getElementById("Employee_employee_code_"+action).innerHTML='';
	var formdata=$('#employee-form').serialize();
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
					 	$.fn.yiiGridView.update('employee-grid');
                    }
					else
					{
						if(data.Employee_fullname)
						{
							document.getElementById("Employee_fullname_"+action).innerHTML=data.Employee_fullname;
						}
						if(data.Employee_employee_code)
						{
							document.getElementById("Employee_employee_code_"+action).innerHTML=data.Employee_employee_code;
							
						}
					}
					
                         
                }
		 
		 });
//
}

</script>