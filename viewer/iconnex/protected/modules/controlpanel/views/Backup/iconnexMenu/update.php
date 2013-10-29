<div class="add_btn clearfix">
			<div class="left"  id="addmenuopen"><input type="text" class="textfield" id="addmenu" /><input type="button" class="green_btn save_btn" value="Save" onclick="addmenu();" /><input type="button" class="btn cancle_btn" value="Cancel" onclick="closemenu();"/></div>
			<div class="right"><a href="javascript:void(0)" class="ui_btn" onclick="openmenu();">Add New Menu</a></div>
		</div>
    	<div class="table">
        	<table>
            	<thead>
                	<tr>
                    	<th>Manage Menu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                	<?php foreach($model as $menu)
					  { ?>
                    
                    <tr>
                    	<td>
						<p class="text" id="menuname_<?php echo $menu->menu_id;?>">
							<a href="javascript:void(0)" class="add_menu"><?php echo $menu->menu_name;?></a>
						</p>
						<input type="text" class="textfield hidden"  value="<?php echo $menu->menu_name;?>" id="menu_<?php echo $menu->menu_id;?>"/>
						
						</td>
                       <td class="btn_block">
                        	<p class="buttonblock1" id="buttonedit"><a href="javascript:void(0)" class="btn edit_btn" onclick="openeditmenu('<?php echo trim($menu->menu_id);?>');">Edit</a><a href="javascript:void(0)"  onclick="deletemenu('<?php echo trim($menu->menu_id);?>')"class="delete">Delete</a></p>
                            <p class="buttonblock2" id="buttonsave<?php echo trim($menu->menu_id);?>"><a href="javascript:void(0)" onclick="editmenu('<?php echo trim($menu->menu_id);?>');"class="green_btn save_btn">Save</a><a href="javascript:void(0)" class="btn cancle_btn" onclick="canceleditmenu('<?php echo trim($menu->menu_id);?>')">Cancel</a></p>
                         </td>
                    </tr>
                    <? }?>
                    
                    
                    
                </tbody>
            </table>
        </div>