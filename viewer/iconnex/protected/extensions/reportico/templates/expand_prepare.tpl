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
{if strlen($STATUSMSG)==0 && strlen($ERRORMSG)==0}
<div style="float:right; ">
{if strlen($MAIN_MENU_URL)>0}
<!--a class="swLinkMenu" style="float:left;" href="{$MAIN_MENU_URL}">&lt;&lt; Menu</a-->
{/if}
</div>
<p>
{if $SHOW_EXPANDED}
							{$T_SEARCH} {$EXPANDED_TITLE} :<br><input  type="text" name="expand_value" size="30" value="{$EXPANDED_SEARCH_VALUE}"</input>
									<input id="prepareAjaxButton" class="swPrpSubmit" type="button" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="{$T_SEARCH}"><br>

{$CONTENT}
							<br>
							<input class="swPrpSubmit" type="button" id="prepareAjaxButton" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="{$T_CLEAR}">
							<input class="swPrpSubmit" type="button" id="prepareAjaxButton" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="{$T_SELECTALL}">
							<input class="swPrpSubmit" type="button" id="prepareAjaxExpand" name="EXPANDOK_{$EXPANDED_ITEM}" value="{$T_OK}">
{/if}
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
						&nbsp;<br>
{$T_DEFAULT_REPORT_DESCRIPTION}
{else}
						&nbsp;<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
{/if}


