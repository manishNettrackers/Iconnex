<?php /* Smarty version 2.6.26, created on 2012-12-04 10:20:38
         compiled from pwi_execute.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
<HTML>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<BODY class="swRepBody">
<div class="swMntForm">
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
				Enter the report project password. <br>(If you have a design mode password then you can enter this to create and configure reports)<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
<?php endif; ?>
<?php endif; ?>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php echo $this->_tpl_vars['CONTENT']; ?>

<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
</div>
</BODY>
</HTML>
<?php endif; ?>