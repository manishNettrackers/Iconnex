<table width="100%">
    <tr><th colspan="2">Available Locations</th><th>Permitted Locations</th></tr>
    <tr>
        <td width="45%">
            <div>
            <?php
            if (empty($data["locsAvailable"]))
                echo "<div>No available locations</div>";
            else
            {
                echo CHtml::textField('q', $search, array('id'=>'q'));
                $ajax = array (
                    'type'=>'POST',
                    'update'=>'#locations',
                    'beforeSend' => 'function(){ $("#loadMess").addClass("loading"); }',
                    'complete' => 'function(){ $("#loadMess").removeClass("loading"); }'
                );
                echo CHtml::ajaxSubmitButton('Search',
                    array('locSearch'),
                    $ajax
                );

                echo CHtml::activeDropDownList(Location::model(),
                    'location_id',
                    CHtml::listData(
                        $data["locsAvailable"],
                        'location_id',
                        'loc_desc'),
                    array('size'=>'20',
                        'multiple'=>'multiple',
                        'class'=>'dropdown',
                        'style'=>'width: 100%;'
                    )
                );
            }
            ?>
            </div>
        </td>
        <td width="10%" align="center">
            <?php
            $ajax = array (
                'type'=>'POST',
                'update'=>'#locations',
                'beforeSend' => 'function(){ $("#loadMess").addClass("loading"); }',
                'complete' => 'function(){ $("#loadMess").removeClass("loading"); }'
            );
            echo CHtml::ajaxSubmitButton('>>',
                array('modify', 'addLocs' => 1),
                $ajax
            );

            echo "<br/>";

            $ajax = array (
                'type'=>'POST',
                'update'=>'#locations',
                'beforeSend' => 'function() { $("#loadMess").addClass("loading"); }',
                'complete' => 'function() { $("#loadMess").removeClass("loading"); }'
            );
            echo CHtml::ajaxSubmitButton('<<',
                array('modify', 'removeLocs' => 1),
                $ajax
            );
            ?>
        </td>
        <td width="45%">
            <div>
            <?php
            if (empty($data["locsPermitted"]))
                echo "<div>No subscribed locations.</div>";
            else
            {
                echo CHtml::activeDropDownList(Location::model(),
                    'location_id',
                    CHtml::listData(
                        $data["locsPermitted"],
                        'location_id',
                        'loc_desc'),
                    array('size'=>'20',
                        'multiple'=>'multiple',
                        'class'=>'dropdown',
                        'style'=>'width: 100%;'
                    )
                );
            }
            ?>
            </div>
        </td>
    </tr>
</table>
<div class="message" id="loadMess">
  <?php echo "&nbsp;".$message ?>
</div>

