{if !$EMBEDDED_REPORT}
<HTML>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
<BODY class="swRepBody">
<div class="swMntForm">
{if strlen($ERRORMSG)>0}
            <TABLE class="swError">
                <TR>
                    <TD>{$ERRORMSG}</TD>
                </TR>
            </TABLE>
{/if}
{if strlen($STATUSMSG)>0} 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if $SHOW_LOGIN}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$PASSWORD_ERROR}</div>
{/if}
				Enter the report project password. <br>(If you have a design mode password then you can enter this to create and configure reports)<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
{/if}
{/if}
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{$CONTENT}
{if !$EMBEDDED_REPORT}
</div>
</BODY>
</HTML>
{/if}
