<?php /* Smarty version 2.6.26, created on 2012-11-29 14:03:10
         compiled from admin.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
<HTML>
<HEAD>
<TITLE><?php echo $this->_tpl_vars['T_ADMINTITLE']; ?>
</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<BODY class="swMenuBody">
<p>
<?php else: ?>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php endif; ?>
<FORM class="swMenuForm" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<div style="height: 78px" class="swAdminBanner">
<div style="float: right;">
<img height="78px" src="images/reportico100.png"/>

</div>
<div style="height: 78px">
<H1 class="swTitle" style="text-align: center; padding-top: 30px; padding-left: 200px;"><?php echo $this->_tpl_vars['T_ADMINTITLE']; ?>
</H1>
</div>
</div>
<input type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" /> 
<?php if ($this->_tpl_vars['SHOW_TOPMENU']): ?>
	<TABLE class="swPrpTopMenu">
		<TR>
<?php if (( $this->_tpl_vars['DB_LOGGEDON'] )): ?>
<?php if (strlen ( $this->_tpl_vars['DBUSER'] ) > 0): ?> 
			<TD class="swPrpTopMenuCell"><?php echo $this->_tpl_vars['T_LOGGED_ON_AS']; ?>
 <?php echo $this->_tpl_vars['DBUSER']; ?>
</TD>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['DBUSER'] ) == 0): ?> 
			<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
<?php endif; ?>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?> 
			<TD style="text-align:center">&nbsp;</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGOUT']): ?>
			<TD width="15%" align="right" class="swPrpTopMenuCell">
				<input class="swPrpSubmit" type="submit" name="adminlogout" value="<?php echo $this->_tpl_vars['T_LOGOFF']; ?>
">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD width="50%"></TD>
			<TD width="35%" align="right" class="swPrpTopMenuCell">
<?php echo $this->_tpl_vars['T_ADMIN_INSTRUCTIONS']; ?>

				<br><input type="password" name="admin_password" value="">
				<input class="swPrpSubmit" type="submit" name="login" value="<?php echo $this->_tpl_vars['T_LOGIN']; ?>
">
<?php if (strlen ( $this->_tpl_vars['ADMIN_PASSWORD_ERROR'] ) > 0): ?>
				<div style="color: #ff0000;"><?php echo $this->_tpl_vars['T_ADMIN_PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
			</TD>
			<TD width="15%" align="right" class="swPrpTopMenuCell">
			</TD>
<?php endif; ?>
		</TR>
	</TABLE>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_SET_ADMIN_PASSWORD']): ?>
<div style="text-align:center;">
<?php if (strlen ( $this->_tpl_vars['SET_ADMIN_PASSWORD_ERROR'] ) > 0): ?>
				<div style="color: #ff0000;"><?php echo $this->_tpl_vars['SET_ADMIN_PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
				<br>
				<br>
<?php echo $this->_tpl_vars['T_SET_ADMIN_PASSWORD_INFO']; ?>

				<br>
<?php echo $this->_tpl_vars['T_SET_ADMIN_PASSWORD_NOT_SET']; ?>

				<br>
<?php echo $this->_tpl_vars['T_SET_ADMIN_PASSWORD_PROMPT']; ?>

				<br>
				<input type="password" name="new_admin_password" value=""><br>
				<br>
<?php echo $this->_tpl_vars['T_SET_ADMIN_PASSWORD_REENTER']; ?>
 <br><input type="password" name="new_admin_password2" value=""><br>
<br>
<br>
<?php if (count ( $this->_tpl_vars['LANGUAGES'] ) > 0): ?>
				<?php echo $this->_tpl_vars['T_CHOOSE_LANGUAGE']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_language">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['LANGUAGES']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo ''; ?><?php if ($this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['active']): ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" selected value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php else: ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php endif; ?><?php echo ''; ?>

<?php endfor; endif; ?>
                </select>
<?php endif; ?>
<br>
				<br>
				<input class="swPrpSubmit" type="submit" name="submit_admin_password" value="<?php echo $this->_tpl_vars['T_SET_ADMIN_PASSWORD']; ?>
">
				<br>
				
</div>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_REPORT_MENU']): ?>
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
<?php if (! $this->_tpl_vars['SHOW_SET_ADMIN_PASSWORD']): ?>
<?php if (count ( $this->_tpl_vars['LANGUAGES'] ) > 0): ?>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_CHOOSE_LANGUAGE']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_language">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['LANGUAGES']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo ''; ?><?php if ($this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['active']): ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" selected value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php else: ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php endif; ?><?php echo ''; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_language" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
<?php endif; ?>
<?php if (count ( $this->_tpl_vars['PROJECT_ITEMS'] ) > 0): ?>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_RUN_SUITE']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_menu_project">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['PROJECT_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_menu_project" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
<?php endif; ?>
<?php endif; ?>
<?php if (count ( $this->_tpl_vars['PROJECT_ITEMS'] ) > 0): ?>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_CREATE_REPORT']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_design_project">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['PROJECT_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_design_project" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_CONFIG_PARAM']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_configure_project">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['PROJECT_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_configure_project" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_DELETE_PROJECT']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_delete_project">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['PROJECT_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_delete_project" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
<?php endif; ?>
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['MENU_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<TR><TD class="swMenuItem">'; ?><?php if ($this->_tpl_vars['MENU_ITEMS'][$this->_sections['menuitem']['index']]['label'] == 'BLANKLINE'): ?><?php echo '&nbsp;'; ?><?php else: ?><?php echo ''; ?><?php if ($this->_tpl_vars['MENU_ITEMS'][$this->_sections['menuitem']['index']]['label'] == 'LINE'): ?><?php echo '<hr>'; ?><?php else: ?><?php echo '<a class="swMenuItemLink" href="'; ?><?php echo $this->_tpl_vars['MENU_ITEMS'][$this->_sections['menuitem']['index']]['url']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['MENU_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</a>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo '</TD></TR>'; ?>

<?php endfor; endif; ?>
		
		<TR> <TD>&nbsp;</TD> </TR>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><a class="swMenuItemLink" href="<?php echo $this->_tpl_vars['DOCDIR']; ?>
/li_reportico.html"><?php echo $this->_tpl_vars['T_DOCUMENTATION']; ?>
</a>
			</TD>
		</TR>
	</TABLE>
<?php else: ?>
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
<?php if (! $this->_tpl_vars['SHOW_SET_ADMIN_PASSWORD']): ?>
<?php if (count ( $this->_tpl_vars['LANGUAGES'] ) > 1): ?>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_CHOOSE_LANGUAGE']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_language">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['LANGUAGES']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo ''; ?><?php if ($this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['active']): ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" selected value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php else: ?><?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['value']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['LANGUAGES'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?><?php endif; ?><?php echo ''; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_language" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
<?php endif; ?>
<?php if (count ( $this->_tpl_vars['PROJECT_ITEMS'] ) > 0): ?>
		<TR> 
			<TD class="swMenuItem" style="width: 30%"><?php echo $this->_tpl_vars['T_RUN_SUITE']; ?>

				<select class="swPrpDropSelectRegular" name="jump_to_menu_project">
<?php unset($this->_sections['menuitem']);
$this->_sections['menuitem']['name'] = 'menuitem';
$this->_sections['menuitem']['loop'] = is_array($_loop=$this->_tpl_vars['PROJECT_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['menuitem']['show'] = true;
$this->_sections['menuitem']['max'] = $this->_sections['menuitem']['loop'];
$this->_sections['menuitem']['step'] = 1;
$this->_sections['menuitem']['start'] = $this->_sections['menuitem']['step'] > 0 ? 0 : $this->_sections['menuitem']['loop']-1;
if ($this->_sections['menuitem']['show']) {
    $this->_sections['menuitem']['total'] = $this->_sections['menuitem']['loop'];
    if ($this->_sections['menuitem']['total'] == 0)
        $this->_sections['menuitem']['show'] = false;
} else
    $this->_sections['menuitem']['total'] = 0;
if ($this->_sections['menuitem']['show']):

            for ($this->_sections['menuitem']['index'] = $this->_sections['menuitem']['start'], $this->_sections['menuitem']['iteration'] = 1;
                 $this->_sections['menuitem']['iteration'] <= $this->_sections['menuitem']['total'];
                 $this->_sections['menuitem']['index'] += $this->_sections['menuitem']['step'], $this->_sections['menuitem']['iteration']++):
$this->_sections['menuitem']['rownum'] = $this->_sections['menuitem']['iteration'];
$this->_sections['menuitem']['index_prev'] = $this->_sections['menuitem']['index'] - $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['index_next'] = $this->_sections['menuitem']['index'] + $this->_sections['menuitem']['step'];
$this->_sections['menuitem']['first']      = ($this->_sections['menuitem']['iteration'] == 1);
$this->_sections['menuitem']['last']       = ($this->_sections['menuitem']['iteration'] == $this->_sections['menuitem']['total']);
?>
<?php echo '<OPTION label="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '" value="'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['PROJECT_ITEMS'][$this->_sections['menuitem']['index']]['label']; ?><?php echo '</OPTION>'; ?>

<?php endfor; endif; ?>
				</select>
				<input class="swMntButton" type="submit" name="submit_menu_project" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
			</TD>
		</TR>
<?php endif; ?>
<?php endif; ?>
		<TR> <TD>&nbsp;</TD> </TR>
	</TABLE>
<?php endif; ?>

	<!--TABLE class="swStatus"><TR><TD>Select a Report From the List Above</TD></TR></TABLE-->
<?php if (strlen ( $this->_tpl_vars['ERRORMSG'] ) > 0): ?> 
			<TABLE class="swError">
				<TR>
					<TD><?php echo $this->_tpl_vars['ERRORMSG']; ?>
</TD>
				</TR>
			</TABLE>
<?php endif; ?>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico <?php echo $this->_tpl_vars['REPORTICO_VERSION']; ?>
</a></div>
</FORM>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
</BODY>
</HTML>
<?php endif; ?>