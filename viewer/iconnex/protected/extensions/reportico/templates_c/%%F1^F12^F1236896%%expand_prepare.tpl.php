<?php /* Smarty version 2.6.26, created on 2012-12-03 20:02:00
         compiled from expand_prepare.tpl */ ?>
<?php if (strlen ( $this->_tpl_vars['ERRORMSG'] ) > 0): ?>
            <TABLE class="swError">
                <TR>
                    <TD><?php echo $this->_tpl_vars['ERRORMSG']; ?>
</TD>
                </TR>
            </TABLE>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['STATUSMSG'] ) > 0): ?> 
			<TABLE class="swStatus">
				<TR>
					<TD><?php echo $this->_tpl_vars['STATUSMSG']; ?>
</TD>
				</TR>
			</TABLE>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['STATUSMSG'] ) == 0 && strlen ( $this->_tpl_vars['ERRORMSG'] ) == 0): ?>
<div style="float:right; ">
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?>
<!--a class="swLinkMenu" style="float:left;" href="<?php echo $this->_tpl_vars['MAIN_MENU_URL']; ?>
">&lt;&lt; Menu</a-->
<?php endif; ?>
</div>
<p>
<?php if ($this->_tpl_vars['SHOW_EXPANDED']): ?>
							<?php echo $this->_tpl_vars['T_SEARCH']; ?>
 <?php echo $this->_tpl_vars['EXPANDED_TITLE']; ?>
 :<br><input  type="text" name="expand_value" size="30" value="<?php echo $this->_tpl_vars['EXPANDED_SEARCH_VALUE']; ?>
"</input>
									<input id="prepareAjaxButton" class="swPrpSubmit" type="button" name="EXPANDSEARCH_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="<?php echo $this->_tpl_vars['T_SEARCH']; ?>
"><br>

<?php echo $this->_tpl_vars['CONTENT']; ?>

							<br>
							<input class="swPrpSubmit" type="button" id="prepareAjaxButton" name="EXPANDCLEAR_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="<?php echo $this->_tpl_vars['T_CLEAR']; ?>
">
							<input class="swPrpSubmit" type="button" id="prepareAjaxButton" name="EXPANDSELECTALL_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="<?php echo $this->_tpl_vars['T_SELECTALL']; ?>
">
							<input class="swPrpSubmit" type="button" id="prepareAjaxExpand" name="EXPANDOK_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="<?php echo $this->_tpl_vars['T_OK']; ?>
">
<?php endif; ?>
<?php if (! $this->_tpl_vars['SHOW_EXPANDED']): ?>
<?php if (! $this->_tpl_vars['REPORT_DESCRIPTION']): ?>
						&nbsp;<br>
<?php echo $this->_tpl_vars['T_DEFAULT_REPORT_DESCRIPTION']; ?>

<?php else: ?>
						&nbsp;<br>
						<?php echo $this->_tpl_vars['REPORT_DESCRIPTION']; ?>

<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

