<?php
    $this->menu=array(array('label'=>'Back to Subscribers', 'url'=>array('subscriber/admin')));
?>
<div>
<?php echo CHtml::beginForm(); ?>
<?php echo CHtml::errorSummary($model); ?>
<table width="100%">
    </tr>
        <th width="50%" colspan="2">Choose permitted locations for 
            <?php
            echo CHtml::activeDropDownList($model,
                'subscriber_id',
                CHtml::listData(Subscriber::model()->findAll(array('order'=>'subscriber_code')), 'subscriber_id', 'subscriber_code'),
                array(
                    'class' => 'dropdown',
                    'ajax' => array('type' => 'GET',
                        'url' => array('locs'),
                        'update' => '#locations',
                        'beforeSend' => 'function(){ $("#loadMess").addClass("loading"); }',
                        'complete' => 'function(){ $("#loadMess").removeClass("loading"); }'
                    )
                )
            );
            ?>
        </th>
    </tr>
    <tr>
        <td width="50%">
            <div id="locations">
                <?php
                    $this->renderPartial('locations', array('model' => $model, 'message' => $message, 'data' => $data, 'search' => ""));
                ?>
            </div>
        </td>
    </tr>
</table>
<br/>
<?php echo CHtml::endForm(); ?>
</div>

