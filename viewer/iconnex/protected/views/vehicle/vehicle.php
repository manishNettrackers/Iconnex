<?php

    ini_set("memory_limit","800M");

    $this->breadcrumbs=array(
	    'Vehicles'=>array('index'),
	    'Manage',
        );

    require_once("topmenustrip.php");

var_dump(Yii::app()->user);

$this->menu=array(
	array('label'=>'List Vehicle', 'url'=>array('index')),
	array('label'=>'Create Vehicle', 'url'=>array('create')),
);

$updateUrl=$this->createUrl('vehicle/update');
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('vehicle-grid', {
		data: $(this).serialize()
	});
	return false;
});
$('#close-button').live('click', function(event){
    $('#output').toggle(false);
    $('#output').attr('innerHTML','Loading');
	return false;
});
    var target = null;
    $('#unit-build-form :input').focus(function() {
        target = this;
        alert(target);
    });

$('#vehicle-form').live('submit', function(event){
     var dataString = $('#vehicle-form').serialize();
    var myform = $('#vehicle-form');
    var formaction =  this.action;
    $.ajax({  
    type: 'GET',  
    url: formaction,
    data: dataString,  
    success: function() {  
        $('#output').toggle(false);
    },
    error: function(xhr, desc, err) {
        alert('no');
    }
    });
    $('#output').addClass('loading');
    $('#output').toggle(false);
    $('#output').attr('innerHTML','Loading');
	return false;
});
");
?>

<?php
    topmenustrip($this, session_menu_request_item ("target_menu", ""));

    $updateUrl=$this->createUrl('vehicle/updatep');
    $buildUrl=$this->createUrl('unitBuild/updateFromVehicle');

    Yii::app()->clientScript->registerScript('initGridEvents',<<<EOD

    $('.view').live("click", function(event) {
        var selectionIds=$.fn.yiiGridView.getSelection('vehicle-grid');

        $("#output").attr('innerHTML','Loading Data..');
        $("#output").addClass("loading");
        $('#output').toggle(true);

        if (selectionIds.length!==0) {
            $.ajax({
                type: 'GET',
                url: '$buildUrl',
                data: {ids: selectionIds},
                dataType: 'html',
                success: function(data, status) {
                    $("#output").attr('innerHTML',data);
                },
                error: function(xhr, desc, err) {
                    alert("no");
                }
            });
        }
        return false;
    });
    $('.update').live("click", function(event) {
        var selectionIds=$.fn.yiiGridView.getSelection('tvehicle-grid');

        $("#output").attr('innerHTML','Loading Data..');
        $("#output").addClass("loading");
        $('#output').toggle(true);

        if (selectionIds.length!==0) {
            $.ajax({
                type: 'GET',
                url: '$updateUrl',
                data: {ids: selectionIds},
                dataType: 'html',
                success: function(data, status) {
                    $("#output").attr('innerHTML',data);
                },
                error: function(xhr, desc, err) {
                    alert("no");
                }
            });
        }
        return false;
    });
EOD
,CClientScript::POS_READY);
?>

<br>
<h1>Manage Vehicles</h1>

<?
//$record=Vehicle::model()->findByAttributes(array('vehicle_code'=>"dupci1"));
//echo get_class($record);
//$bd = $record->build_id;
//$bd = $record->vehicle_reg;
//var_dump( $record->unit_build->build_code);
//echo get_class($record);
//echo get_class($record->unit_build);
?>


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

<div id="left">
  <!-- render a list of people for instance -->
  <!--?php $this->widget('zii.widgets.CListView', array('dataProvider'=>$dataProvider, 'itemView'=>'subView'));?-->
  <!--?php $this->widget('zii.widgets.CListView', array('dataProvider'=>$model->search(), 'itemView'=>'vehicle'));?-->
</div>

<div id="right">
  <!--?php $this->renderPartial('personDetail', array('model'=>$model)); ?-->
</div>
<?php 
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'wvehicle-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'vehicle_code',
		'vehicle_reg',
        array
        (
                  'name'=>'vehicle_id',
                  'visible'=> false,
                  'value'=>'$data->unit_build ? $data->unit_build->build_code : "<Unallocated>"',
        ),
        array
        (
                  'name'=>'build_code',
                  'visible'=> Yii::app()->user->allowedAccess('task', 'Vehicle Manager,Administrator'),
                  'htmlOptions'=>array('style'=>'text-align: center'),
                  'value'=>'$data->unit_build ? $data->unit_build->build_code : "<Unallocated>"',
        ),
        array
        (
                'name'=>'vehicle_type_id',
                'filter' => CHtml::listData(VehicleType::model()->findAll(), 'vehicle_type_id', 'vehicle_type_code'), // fields from country table
                'value' => 'VehicleType::Model()->FindByPk($data->vehicle_type_id)->vehicle_type_code',
        ),
		'orun_code',
		/*
		'vetag_indicator',
		'modem_addr',
		'build_id',
		'wheelchair_access',
		*/
        array
        (
			'class'=>'ButtonColumnEx',
						'template'=>'{vehicle} {build} {parameters}',
						'buttons'=>array(
                        'vehicle' => array(
							    'options'=>'array("title"=>"Edit Vehicle", "id"=>"vehedit$data->vehicle_id")',
    							'url'=>'Yii::app()->createUrl("/vehicle/updatep")',
          							"imageUrl"=>Yii::app()->request->baseUrl."/images/vehicle.png",

      							'ajax'=>'
        							array(
          							"url"=>Yii::app()->createUrl("/vehicle/updatep"),
          							"data"=>array("ids[]"=>$data->vehicle_id),
          							"update"=>"#output",
    								"beforeSend" => "function(){
      									$(\'#output\').toggle(true);
      									$(\'#output\').addClass(\'loading\');
        							}",
    								"complete" => "function(data,status){
        								if ( status == \'error\' )
        								{
            								$(\'#output\').toggle(false);
            								alert ( \'Unable to proceed - no data found for request\');
        								}
      								$(\'#output\').removeClass(\'loading\');
        							}",
								);'
                        ),
                        'build' => array(
							    'options'=>'array("title"=>"Edit Build", "id"=>"pub$data->vehicle_id")',
    							'url'=>'Yii::app()->createUrl("/unitBuild/updateFromVehicle")',
          							"imageUrl"=>Yii::app()->request->baseUrl."/images/spanner.png",

      							'ajax'=>'
        							array(
          							"url"=>Yii::app()->createUrl("/unitBuild/updateFromVehicle"),
          							"data"=>array("ids"=>$data->vehicle_id),
          							"update"=>"#output",
    								"beforeSend" => "function(){
      									$(\'#output\').toggle(true);
      									$(\'#output\').addClass(\'loading\');
        							}",
    								"complete" => "function(data,status){
        								if ( status == \'error\' )
        								{
            								$(\'#output\').toggle(false);
            								alert ( \'Unable to proceed - no data found for request\');
        								}
      								$(\'#output\').removeClass(\'loading\');
        							}",
								);'
                        ),
                        'parameters' => array(
							    'options'=>'array("title"=>"Parameters", "id"=>"prm$data->vehicle_id")',
    							'url'=>'Yii::app()->createUrl("/component/component")',
          							"imageUrl"=>Yii::app()->request->baseUrl."/images/params.png",

      							'ajax'=>'
        							array(
          							"url"=>Yii::app()->createUrl("/component/component"),
          							"data"=>array("ids"=>$data->build_id),
          							"update"=>"#output",
    								"beforeSend" => "function(){
      									$(\'#output\').toggle(true);
      									$(\'#output\').addClass(\'loading\');
        							}",
    								"complete" => "function(data,status){
        								if ( status == \'error\' )
        								{
            								$(\'#output\').toggle(false);
            								alert ( \'Unable to proceed - no data found for request\');
        								}
      								$(\'#output\').removeClass(\'loading\');
        							}",
								);'
                        ),
                ),
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>

<?php
/*
if ( Yii::app()->user->allowedAccess('task', 'Vehicle Manager,Administrator'))
{
    gridViewAjaxButton ( "Build", $this->createUrl('unitBuild/updateFromVehicle') );
    gridViewAjaxButton ( "Vehicle", $this->createUrl('vehicle/updatep') );
}
else
{
    gridViewAjaxButton ( "Build", $this->createUrl('unitBuild/view') );
    gridViewAjaxButton ( "Vehicle", $this->createUrl('vehicle/view') );
}
*/

function gridViewAjaxButton ( $label, $url )
{
    echo CHtml::ajaxButton (
    $label, //label
    $url,
    array (
    'type' => 'GET',
    'update' => '#output',
    'data' => 'js:{ids: $.fn.yiiGridView.getSelection("vehicle-grid")}',
    'beforeSend' => 'function(){
      $("#output").toggle(true);
      $("#output").addClass("loading");
        }',
    'complete' => 'function(data,status){
        if ( status == "error" )
        {
            $("#output").toggle(false);
            alert ( "Unable to proceed - no data found for request");
        }
      $("#output").removeClass("loading");
        }',
    ),
    array ( 'class' => 'formButton' )
 );
}
?>

<div id="output" style="display: none; width: 50%; border: 1px solid #666666; background-color: #ffffff; text-align: left; position: fixed; top: 30px; left: 25%; " >
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
&nbsp;<br>
</div>
