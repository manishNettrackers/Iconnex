{if !$EMBEDDED_REPORT} 
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="seekwell_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
</HEAD>
<BODY class="swPrpBody">
{else}
<LINK id="seekwell_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
        <div id="criteria" class="div30">

<form name="topmenu" style="width: 100%" method="POST" action="http://www.google.co.uk/" target="_blank" onsubmit="respondToClick(this); return false;" id="criteriaForm">
<input type="hidden" name="session_name" value="{$SESSION_ID}" />
{if $SHOW_CRITERIA}
	<TABLE class="swPrpCritBox" cellpadding="0">
		<tr>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD class="swPrpExpandCell" rowspan="0" valign="top">
{if $SHOW_EXPANDED}
							Search {$EXPANDED_TITLE} :<br><input  type="text" name="expand_value" size="30" value="{$EXPANDED_SEARCH_VALUE}"</input>
									<input class="swPrpSubmit" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br>

{$CONTENT}
							<br>
							<input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="Select All">
							<input class="swPrpSubmit" type="submit" name="EXPANDOK_{$EXPANDED_ITEM}" value="OK">
{/if}
{if !$SHOW_EXPANDED}
{if $REPORT_DESCRIPTION}
						&nbsp<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
		<TR>
			<TD class="swPrpCritEntry">
				<TABLE class="swPrpCritEntryBox">
{section name=critno loop=$CRITERIA_ITEMS}
					<tr class="swPrpCritLine">
						<td class='swPrpCritTitle'>
							{$CRITERIA_ITEMS[critno].title}
						</td>
						<td class="swPrpCritSel">
							{$CRITERIA_ITEMS[critno].entry}
						</td>
						<td class="swPrpCritExpandSel">
{if $CRITERIA_ITEMS[critno].expand}
							<input class="swPrpCritExpandButton" onclick="window.clicked=this.name; return true;" type="submit" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="...">
{/if}
						</td>
					</TR>
{/section}
				</TABLE>
			</td>
		</tr>
		<TR>
			<TD>
							<TABLE width="100%">
								<tr>
									<td colspan=2>
										<!--input type="submit" class="swPrpSubmit" name="submit" value="Execute"-->
                                        <input type="submit" value="Execute" class="swPrpSubmit" id="sumbmitButton" name="submitJS" onclick="window.clicked = this.value; return true;">
										<input type="submit" class="swPrpSubmit" onclick="window.clicked = this.name; return true;" name="clearform" value="Reset">
									</TD>
								</TR>
							</TABLE>
			</TD>
		</TR>
<!---->
{if $SHOW_OUTPUT}
					<tr>
						<td class="swPrpOutputBox" colspan=2>
						</td>
					</tr>
			</TABLE>

{/if}
{/if}
			<!---->
{if strlen($STATUSMSG)>0 && 1 == 0 } 
			<TABLE class="swStatus">
				<TR>
					<TD>{$STATUSMSG}</TD>
				</TR>
			</TABLE>
{/if}
{if strlen($ERRORMSG)>0} 
			<TABLE class="swError">
				<TR>
					<TD>{$ERRORMSG}</TD>
				</TR>
			</TABLE>
{/if}
</FORM>
        </div>
        <div id="ttbresults" class="div70">
...
        </div>

{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}
