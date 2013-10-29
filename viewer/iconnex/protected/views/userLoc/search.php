<table width="100%">
    <tr><th colspan="2">Available Locations</th><th>Permitted Locations</th></tr>
    <tr>
        <td>
            Filter by Service: 
            <?php
/*            <input type="text" id="service" name="service" />*/
/*
            $ajax = array (
                'type'=>'POST',
                'update'=>'#locations',
                'beforeSend' => 'function() { $("#loadMess").addClass("loading"); }',
                'complete' => 'function() { $("#loadMess").removeClass("loading"); }'
            );
            echo CHtml::ajaxInputField("text", Location->model(), 
                array('modify', 'removeLocs' => 1),
                $ajax
            );
*/
            ?>
            <br/>
            <?php
/*
            echo CHtml::activeDropDownList(Location::model(),
                'location_id',
                CHtml::listData($dataProvider->getData(), 'location_id', 'loc_desc'),
                array('size'=>20,
                    'multiple'=>'multiple',
                    'style'=>'width: 205px'
                )
            );
*/
?>

<?php
echo CHtml::beginForm(CHtml::normalizeUrl(array('userLoc/locSearch')), 'get', array('id'=>'filter-form'))
    . CHtml::textField('string', (isset($_GET['string'])) ? $_GET['string'] : '', array('id'=>'string'))
    . CHtml::submitButton('Search', array('name'=>''))
    . CHtml::endForm();
/*
Yii::app()->clientScript->registerScript('search',
    "var ajaxUpdateTimeout;
    var ajaxRequest;
    $('input#string').keyup(function(){
        ajaxRequest = $(this).serialize();
        clearTimeout(ajaxUpdateTimeout);
        ajaxUpdateTimeout = setTimeout(function () {
            $.fn.yiiListView.update(
                'ajaxListView',
                {data: ajaxRequest}
            )
        },
        300);
    });"
);
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view',
    'sortableAttributes'=>array(
        'id'=>'location_id',
        'transaction'
    ),
    'id'=>'ajaxListView',
));

*/
    echo CHtml::activeDropDownList(Location::model(),
        'location_id',
        CHtml::listData(
            $data['locsAvailable'],
            'location_id',
            'loc_desc'),
        array('size'=>'20',
            'multiple'=>'multiple',
            'class'=>'dropdown',
            'style'=>'width: 100%;'
        )
    );

?>
        </td>
    </tr>
</table>

<div class="message" id="loadMess">
  <?php echo "&nbsp;".$message ?>
</div>

