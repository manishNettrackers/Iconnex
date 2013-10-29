<?php /* Smarty version 2.6.26, created on 2012-12-01 10:59:57
         compiled from maintain.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html>
<HEAD>
<TITLE><?php echo $this->_tpl_vars['TITLE']; ?>
</TITLE>
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<BODY class="swMntBody">
<?php endif; ?>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<FORM class="swMntForm" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<H1 class="swTitle"><?php echo $this->_tpl_vars['TITLE']; ?>
</H1>
<?php if (strlen ( $this->_tpl_vars['STATUSMSG'] ) > 0): ?> 
			<TABLE class="swStatus">
				<TR>
					<TD><?php echo $this->_tpl_vars['STATUSMSG']; ?>
</TD>
				</TR>
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
<input type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" />
<?php if ($this->_tpl_vars['SHOW_TOPMENU']): ?>
	<TABLE class="swMntTopMenu">
		<TR>
<?php if (( $this->_tpl_vars['DB_LOGGEDON'] )): ?> 
			<TD class="swPrpTopMenuCell">
<?php if (( $this->_tpl_vars['DBUSER'] )): ?>
Logged On As <?php echo $this->_tpl_vars['DBUSER']; ?>

<?php else: ?>
&nbsp;
<?php endif; ?>
			</TD>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?> 
			<TD style="text-align: left;">
				<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['MAIN_MENU_URL']; ?>
"><?php echo $this->_tpl_vars['T_PROJECT_MENU']; ?>
</a>
				&nbsp;<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['ADMIN_MENU_URL']; ?>
"><?php echo $this->_tpl_vars['T_ADMIN_MENU']; ?>
</a>
				&nbsp;<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['RUN_REPORT_URL']; ?>
"><?php echo $this->_tpl_vars['T_RUN_REPORT']; ?>
</a>
				&nbsp;<input class="swLinkMenu" type="submit" name="submit_prepare_mode" style="display:none" onclick="return(false);" value="Do nothing on enter">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_MODE_MAINTAIN_BOX'] && 0): ?>
			<TD style="text-align: left;">
				<input class="swMntButton" type="submit" name="submit_genws_mode" value="<?php echo $this->_tpl_vars['T_GEN_WEB_SERVICE']; ?>
">
			</TD>
			<TD style="text-align: right;">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGOUT']): ?>
			<TD style="width: 15%; text-align: right; padding-right: 10px;" align="right" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="<?php echo $this->_tpl_vars['T_LOGOFF']; ?>
">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD style="width: 50%"></TD>
			<TD style="width: 35%" align="right" class="swPrpTopMenuCell">
<?php if (strlen ( $this->_tpl_vars['PASSWORD_ERROR'] ) > 0): ?>
                                <div style="color: #ff0000;"><?php echo $this->_tpl_vars['PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
				<?php echo $this->_tpl_vars['T_DESIGN_PASSWORD_PROMPT']; ?>
 <input type="password" name="project_password" value="">
			</TD>
			<TD style="width: 15%" align="right" class="swPrpTopMenuCell">
				<input class="swPrpSubmit" type="submit" name="login" value="<?php echo $this->_tpl_vars['T_LOGIN']; ?>
">
			</TD>
<?php endif; ?>
		</TR>
	</TABLE>
<?php endif; ?>
	<TABLE class="swMntMainBox" cellspacing="0" cellpadding="0">
		<TR>
			<TD>
<?php echo $this->_tpl_vars['CONTENT']; ?>

			</TD>
		</TR>
	</TABLE>
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico <?php echo $this->_tpl_vars['REPORTICO_VERSION']; ?>
</a></div>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
</BODY>
</HTML>
<?php endif; ?>

<?php echo '
<script type="text/javascript">var ie7 = false;</script>
<!--[if IE 7]>
<script type="text/javascript">ie7 = true;</script>
<![endif]-->

<script language="javascript"> 
// Shows and hides a block of design items fields
function toggleLine(id) {

    var a = this;
    var nm = a.id;
    var togbut = document.getElementById(id);
    var ele = document.getElementById("toggleText");
    var elems = document.getElementsByTagName(\'*\'),i;
    for (i in elems)
    {
		if ( ie7 )
		{
        	if((" "+elems[i].className+" ").indexOf(" "+id+" ") > -1)
			{
            	if(elems[i].style.display == "inline") {
                	elems[i].style.display = "none";
                	togbut.innerHTML = "+";
            	}
            	else {
                	togbut.innerHTML = "-";
                	elems[i].style.display = "";
                	elems[i].style.display = "inline";
            	}
			}
		}
		else
		{
        	if((" "+elems[i].className+" ").indexOf(" "+id+" ") > -1)
			{
            	if(elems[i].style.display == "table-row") {
                	elems[i].style.display = "none";
                	togbut.innerHTML = "+";
            	}
            	else {
                	togbut.innerHTML = "-";
                	elems[i].style.display = "";
                	elems[i].style.display = "table-row";
            	}
			}
		}
    }
} 
</script>
'; ?>

