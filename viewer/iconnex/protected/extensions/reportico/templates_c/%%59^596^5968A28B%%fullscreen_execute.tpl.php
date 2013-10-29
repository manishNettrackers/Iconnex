<?php /* Smarty version 2.6.26, created on 2012-12-05 11:38:08
         compiled from fullscreen_execute.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
<HTML>
<HEAD>
<TITLE>oo<?php echo $this->_tpl_vars['TITLE']; ?>
</TITLE>
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php if ($this->_tpl_vars['REPORT_PAGE_STYLE']): ?>
<BODY class="swRepBody" <?php echo $this->_tpl_vars['REPORT_PAGE_STYLE']; ?>
;">
<?php else: ?>
<BODY class="swRepBody">
<?php endif; ?>
<div class="swRepForm">
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
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
<?php if (strlen ( $this->_tpl_vars['PASSWORD_ERROR'] ) > 0): ?>
                                <div style="color: #ff0000;"><?php echo $this->_tpl_vars['PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
<?php endif; ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['EMBEDDED_REPORT']): ?>
<!--LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
"-->
<?php endif; ?>
<FORM class="swPrpForm" id="critform" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<input type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" />
<input type="submit" id="fullScreenReturn" name="fullScreenReturn" value="Return">
<?php echo $this->_tpl_vars['CONTENT']; ?>

</FORM>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
</div>
</BODY>
</HTML>
<?php endif; ?>