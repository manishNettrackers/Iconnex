{if !$EMBEDDED_REPORT} 
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{$OUTPUT_ENCODING}
</HEAD>
<BODY class="swPrpBody">
{else}
{/if}

{literal}
<STYLE>
#fullScreenGo, #fullScreenCSV, #fullScreenPDF, #fullScreenReset
{
	color: #555555;
	border-color: #555555;
}
</STYLE>
{/literal}

{literal}
 <STYLE type="text/css">
#contextmenu li
{
	float:left;
	list-style-type: none;
    padding-right: 10px;
    padding-left: 10px;
    text-align: center;
    height:  20px;
    color: #000000;
    text-decoration: none;
    border: 1px solid #D0CCC9;
    background-color: #EFEFEF;
    padding-top: 10px;
    padding-bottom: 5px;
    margin-right: 5px;
    margin-bottom: 10px;
	cursor: pointer;
}
 </STYLE>
{/literal}
<FORM class="swPrpForm" id="critform" name="topmenu" method="GET" action="{$SCRIPT_SELF}">
<!--h1 class="swTitle">{$TITLE}</h1-->
<input type="hidden" name="session_name" value="{$SESSION_ID}" />
{if false && $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
{if ($DB_LOGGEDON)} 
			<TD style="width: 10px" class="swPrpTopMenuCell">
			</TD>
{/if}
			<TD style="text-align:left">
{if $SHOW_ADMIN_BUTTON}
{if strlen($ADMIN_MENU_URL)>0} 
                <a class="swLinkMenu" href="{$ADMIN_MENU_URL}">{$T_ADMIN_MENU}</a>
{/if}
{/if}
{if strlen($MAIN_MENU_URL)>0} 
{if $SHOW_PROJECT_MENU_BUTTON}
				<!--a class="swLinkMenu" href="{$MAIN_MENU_URL}">{$T_PROJECT_MENU}</a-->
{/if}
{if $SHOW_DESIGN_BUTTON}
                                &nbsp;<input class="swLinkMenu" type="submit" name="submit_design_mode" value="{$T_DESIGN_REPORT}">
{/if}

{/if}
			</TD>
{if $SHOW_LOGOUT}
			<TD style="width:15%; text-align: right; padding-right: 10px;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="{$T_LOGOFF}">
			</TD>
{/if}
{if $SHOW_LOGIN}
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$T_PASSWORD_ERROR}</div>
{/if}
				{$T_ENTER_PROJECT_PASSWORD}<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="{$T_LOGIN}">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
{if $SHOW_CRITERIA}
	<TABLE class="swPrpCritBox" id="critbody" style="margin-bottom: 0px" cellpadding="0">
{if $SHOW_OUTPUT}
								<TR>
									<td width="2%">
										&nbsp;
									</TD>
									<TD width="40%">
<h1  style="border: none; padding: none; margin: none;" class="swTitle">{$TITLE}</h1>
{if false && $SHOW_DESIGN_BUTTON}
<br>
										&nbsp;
										{$T_OUTPUT}
											<INPUT type="radio" name="target_format" value="HTML" {$OUTPUT_TYPES[0]}>HTML
											<INPUT type="radio" name="target_format" value="PDF" {$OUTPUT_TYPES[1]}>PDF
											<INPUT type="radio" name="target_format" value="CSV" {$OUTPUT_TYPES[2]}>CSV
{if $SHOW_DESIGN_BUTTON}
											<INPUT type="radio" name="target_format" value="XML" {$OUTPUT_TYPES[3]}>XML
											<INPUT type="radio" name="target_format" value="JSON" {$OUTPUT_TYPES[4]}>JSON
{/if}
                                    </td>
{/if}
									<td width="50%" style="vertical-align: bottom">
                                        {$T_SHOW}<BR>
										<!--INPUT type="checkbox" name="target_attachment" value="1" {$OUTPUT_ATTACH}>As Attachment</INPUT-->
										<INPUT type="checkbox" name="target_show_criteria" value="1" {$OUTPUT_SHOWCRITERIA}>{$T_SHOW_CRITERIA}</INPUT>
										<INPUT type="checkbox" name="target_show_group_headers" value="1" {$OUTPUT_SHOWGROUPHEADERS}>{$T_SHOW_GRPHEADERS}</INPUT>
										<INPUT type="checkbox" name="target_show_detail" value="1" {$OUTPUT_SHOWDETAIL}>{$T_SHOW_DETAIL}</INPUT>
                                        
										<INPUT type="checkbox" name="target_show_group_trailers" value="1" {$OUTPUT_SHOWGROUPTRAILERS}>{$T_SHOW_GRPTRAILERS}</INPUT>
										<INPUT type="checkbox" name="target_show_column_headers" value="1" {$OUTPUT_SHOWCOLHEADERS}>{$T_SHOW_COLHEADERS}</INPUT>
{if $OUTPUT_SHOW_SHOWGRAPH}
										<INPUT type="checkbox" name="target_show_graph" value="1" {$OUTPUT_SHOWGRAPH}>{$T_SHOW_GRAPH}</INPUT><BR>
{/if}
									</td>
{if false && $OUTPUT_SHOW_DEBUG}
									<td width="4%" style="vertical-align: top">
{if $SHOW_DESIGN_BUTTON}

										{$T_DEBUG_LEVEL}
										<SELECT class="swRunMode" name="debug_mode">';
											<OPTION {$DEBUG_NONE} label="None" value="0">{$T_DEBUG_NONE}</OPTION>
											<OPTION {$DEBUG_LOW} label="Low" value="1">{$T_DEBUG_LOW}</OPTION>
											<OPTION {$DEBUG_MEDIUM} label="Medium" value="2">{$T_DEBUG_MEDIUM}</OPTION>
											<OPTION {$DEBUG_HIGH} label="High" value="3">{$T_DEBUG_HIGH}</OPTION>
										</SELECT>
{/if}
										<BR>
									</td>
{/if}
								</TR>
{else}
{/if}
	</TABLE>
<div id="criteriabody">
	<TABLE class="swPrpCritBox" cellpadding="0">
<!---->
		<TR id="swPrpCriteriaBody">
			<TD class="swPrpCritEntry">
			<div id="swPrpSubmitPane">
    				<input type="submit" id="fullScreenGo" name="fullScreenGo" value="Run Report">
    				<input type="submit" id="fullScreenCSV" name="fullScreenCSV" value="CSV">
    				<input type="submit" id="fullScreenPDF" name="fullScreenPDF" value="PDF">
    				<!--input type="submit" id="prepareAjaxExecute" name="submitPrepare" value="{$T_GO}"-->
    				<input type="submit" id="fullScreenReset" name="clearform" value="{$T_RESET}">
			</div>
                <TABLE class="swPrpCritEntryBox"">
{if isset($CRITERIA_ITEMS)}
{section name=critno loop=$CRITERIA_ITEMS}
                    <tr class="swPrpCritLine" id="criteria_{$CRITERIA_ITEMS[critno].name}">
                        <td class='swPrpCritTitle'>
                            {$CRITERIA_ITEMS[critno].title}
                        </td>
                        <td class="swPrpCritSel">
                            {$CRITERIA_ITEMS[critno].entry}
                        </td>
                        <td class="swPrpCritExpandSel">
{if $CRITERIA_ITEMS[critno].expand}
{if $AJAX_ENABLED} 
                            <input class="swPrpCritExpandButton" id="prepareAjaxButton" type="button" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="&nbsp;&nbsp;">
{else}
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="{$T_EXPAND}">
{/if}
{/if}
                        </td>
                    </TR>
{/section}
{/if}
                </TABLE>
{if isset($CRITERIA_ITEMS)}
{if count($CRITERIA_ITEMS) > 1}
<div id="swPrpSubmitPane">
	<input type="submit" id="fullScreenGo" name="fullScreenGo" value="Run Report">
   	<input type="submit" id="fullScreenCSV" name="fullScreenCSV" value="CSV">
	<input type="submit" id="fullScreenPDF" name="fullScreenPDF" value="PDF">
    <input type="submit" id="fullScreenReset" name="clearform" value="{$T_RESET}">
</div>
{/if}
{/if}
			</td>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD class="swPrpExpandCell" id="swPrpExpandCell" rowspan="0" valign="top">
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
									<input id="prepareAjaxButton" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br>

{$CONTENT}
							<br>
							<input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="Select All">
							<input class="swPrpSubmit" type="submit" name="EXPANDOK_{$EXPANDED_ITEM}" value="OK">
{/if}
						<div class="swPrpHelp" id="swPrpHelp" style="width: 100%;">
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
{$T_DEFAULT_REPORT_DESCRIPTION}
{else}
						&nbsp<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
						</div>
{/if}
						<TD class="swPrpExpandCell" id="swPrpExpandCell" rowspan="0" valign="top">
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>

{/if}
</div>
			<!---->

	</TABLE>
</FORM>
<div class="smallbanner"></DIV>
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}


