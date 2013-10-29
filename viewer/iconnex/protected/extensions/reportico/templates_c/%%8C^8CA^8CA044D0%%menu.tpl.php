<?php /* Smarty version 2.6.26, created on 2012-11-29 14:03:18
         compiled from menu.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
<HTML>
<HEAD>
<TITLE><?php echo $this->_tpl_vars['TITLE']; ?>
</TITLE>
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<BODY class="swMenuBody">
<?php else: ?>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php endif; ?>
<FORM class="swMenuForm" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<H1 class="swTitle"><?php echo $this->_tpl_vars['TITLE']; ?>
</H1>
<input type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" /> 
<?php if ($this->_tpl_vars['SHOW_TOPMENU']): ?>
	<TABLE class="swPrpTopMenu">
		<TR>
                        <TD class="swPrpTopMenuCell">
			<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['ADMIN_MENU_URL']; ?>
"><?php echo $this->_tpl_vars['T_ADMIN_HOME']; ?>
</a>
<?php if (( $this->_tpl_vars['SHOW_DESIGN_BUTTON'] )): ?>
			<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['CONFIGURE_PROJECT_URL']; ?>
"><?php echo $this->_tpl_vars['T_CONFIG_PROJECT']; ?>
</a>
			<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['CREATE_REPORT_URL']; ?>
"><?php echo $this->_tpl_vars['T_CREATE_REPORT']; ?>
</a>
<?php endif; ?>
<?php if (count ( $this->_tpl_vars['LANGUAGES'] ) > 1 || ( $this->_tpl_vars['SHOW_DESIGN_BUTTON'] )): ?>
&nbsp; &nbsp; &nbsp; &nbsp;
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
                <input class="swMntButton" type="submit" name="submit_language" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
<?php endif; ?>
			</TD>
<?php if (strlen ( $this->_tpl_vars['DBUSER'] ) > 0): ?> 
			<TD class="swPrpTopMenuCell"><?php echo $this->_tpl_vars['T_LOGGED_IN_AS']; ?>
 <?php echo $this->_tpl_vars['DBUSER']; ?>
</TD>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['DBUSER'] ) == 0): ?> 
			<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?> 
			<TD style="text-align:center">&nbsp;</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGOUT']): ?>
			<TD width="15%" style="padding-left: 10px; text-align: right;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="<?php echo $this->_tpl_vars['T_LOGOFF']; ?>
">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
<br><br><br><br>
<?php if (strlen ( $this->_tpl_vars['PASSWORD_ERROR'] ) > 0): ?>
                                <div style="color: #ff0000;"><?php echo $this->_tpl_vars['PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
<?php echo $this->_tpl_vars['T_ENTER_PROJECT_PASSWORD']; ?>
<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="<?php echo $this->_tpl_vars['T_LOGIN']; ?>
"><br><br><br><br><br>
			</TD>
<?php endif; ?>
		</TR>
	</TABLE>
<?php endif; ?>

<?php if ($this->_tpl_vars['SHOW_REPORT_MENU']): ?>
	<TABLE class="swMenu">
		<TR> <TD>&nbsp;</TD> </TR>
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
	</TABLE>
<?php endif; ?>

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
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
</BODY>
</HTML>
<?php endif; ?>