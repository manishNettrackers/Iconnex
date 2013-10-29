{if !$EMBEDDED_REPORT} 
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
</HEAD>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
<BODY class="swMenuBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
<FORM class="swMenuForm" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<br>
<input type="hidden" name="session_name" value="{$SESSION_ID}" /> 
{if $SHOW_TOPMENU && ( $SHOW_LOGIN || $SHOW_LOGOUT ) }
	<TABLE class="swPrpTopMenu">
		<TR>
{if ($DB_LOGGEDON)}
{if strlen($DBUSER)>0} 
			<TD class="swPrpTopMenuCell">Logged On As {$DBUSER}</TD>
{/if}
{if strlen($DBUSER)==0} 
			<TD style="width: 15%" class="swPrpTopMenuCell">&nbsp;</TD>
{/if}
{/if}
{if strlen($MAIN_MENU_URL)>0} 
			<TD style="text-align:center">&nbsp;</TD>
{/if}
{if $SHOW_MODE_BOX && 0 }
			<TD style="width: 30%">Mode 
				<select class="swRunMode" name="execute_mode">
					<OPTION label="MAINTAIN" value="MAINTAIN">Maintain</OPTION>
					<OPTION selected label="PREPARE" value="PREPARE">Prepare</OPTION>
				</select>
				<input class="swMntButton" type="submit" name="submit_execute_mode" value="Go">
			</TD>
{/if}
{if $SHOW_LOGOUT && 1 == 0}
			<TD width="15%" align="right" class="swPrpTopMenuCell">
				<input class="swPrpSubmit" type="submit" name="logout" value="Log Off">
			</TD>
{/if}
{if $SHOW_LOGIN && 1 == 0 }
			<TD width="50%"></TD>
			<TD width="35%" align="right" class="swPrpTopMenuCell">
				User Id <input type="text" name="userid" value=""><br>
				Password <input type="password" name="password" value="">
			</TD>
			<TD width="15%" align="right" class="swPrpTopMenuCell">
				<input class="swPrpSubmit" type="submit" name="login" value="Login">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}

{section name=menuitem loop=$MENU_ITEMS}
{strip}
			<DIV class="swMenuItem">
{if $MENU_ITEMS[menuitem].label == "BLANKLINE"}
				&nbsp;
{else}
{if $MENU_ITEMS[menuitem].label == "LINE"}
				<hr>
{else}
				<a class="swMenuItemLink" href="{$MENU_ITEMS[menuitem].url}">{$MENU_ITEMS[menuitem].label}</a>
{/if}
{/if}
			</DIV>
{/strip}
{/section}
		
	<!--TABLE class="swStatus"><TR><TD>Select a Report From the List Above</TD></TR></TABLE-->
	<!--TABLE class="swPrpTopMenu">
		<TR>
		<TD>&nbsp;</TD>
		</TR>
	</TABLE-->
{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
<br>
</FORM>
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
