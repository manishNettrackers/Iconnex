{if !$EMBEDDED_REPORT} 
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
</HEAD>
<BODY class="swPrpBody">
{else}
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}


{if $AJAX_ENABLED} 
<script type="text/javascript" src="{$JSPATH}/jquery.js"></script>
<script type="text/javascript" src="{$JSPATH}/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="{$JSPATH}/ui/jquery.ui.datepicker.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="{$JSPATH}/ui/themes/base/jquery.ui.all.css">
{/if}

<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="GET" action="{$SCRIPT_SELF}">
<h1 class="swTitle">{$TITLE}</h1>
<input type="hidden" name="r" value="infohost" />
<input type="hidden" name="session_name" value="{$SESSION_ID}" />
{if $SHOW_TOPMENU}
	<TABLE class="swPrpTopMenu">
		<TR>
{if ($DB_LOGGEDON)} 
			<TD style="width: 10px" class="swPrpTopMenuCell">
			</TD>
{/if}
{if strlen($MAIN_MENU_URL)>0} 
			<TD style="text-align:left">
				<a class="swLinkMenu" href="{$MAIN_MENU_URL}">Project Menu</a>
{if $SHOW_DESIGN_BUTTON}
                                &nbsp;<input class="swLinkMenu" type="submit" name="submit_design_mode" value="Design Report">
{/if}

{/if}
			</TD>
{if $SHOW_LOGOUT && 1 == 0 }
			<TD style="width:15%; text-align: right; padding-right: 10px;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="Log Off">
			</TD>
{/if}
{if $SHOW_LOGIN && 1 == 0 }
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
{if strlen($PASSWORD_ERROR) > 0}
                                <div style="color: #ff0000;">{$PASSWORD_ERROR}</div>
{/if}
				Enter the report project password. <br>(If you have a design mode password then you can enter this to create and configure reports)<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
{/if}
		</TR>
	</TABLE>
{/if}
<div id="criteriabody">
{if $SHOW_CRITERIA}
	<TABLE class="swPrpCritBox" id="critbody" cellpadding="0">
{if $SHOW_OUTPUT}
								<TR>
									<td width="10%">
										&nbsp;
									</TD>
									<TD width="40%">
										&nbsp;
										Output :
											<INPUT type="radio" name="target_format" value="HTML" {$OUTPUT_TYPES[0]}>HTML
											<INPUT type="radio" name="target_format" value="PDF" {$OUTPUT_TYPES[1]}>PDF
											<INPUT type="radio" name="target_format" value="CSV" {$OUTPUT_TYPES[2]}>CSV
											<INPUT type="radio" name="target_format" value="XML" {$OUTPUT_TYPES[3]}>XML
											<INPUT type="radio" name="target_format" value="JSON" {$OUTPUT_TYPES[4]}>JSON
									<td width="30%" style="vertical-align: top">
										<!--INPUT type="checkbox" name="target_attachment" value="1" {$OUTPUT_ATTACH}>As Attachment</INPUT-->
										&nbsp;
										<INPUT type="checkbox" name="target_show_criteria" value="1" {$OUTPUT_SHOWCRITERIA}>Show Criteria</INPUT>
{if $OUTPUT_SHOW_SHOWGRAPH}
										<INPUT type="checkbox" name="target_show_graph" value="1" checked>Show Graph</INPUT><BR>
{/if}
									</td>
{if $OUTPUT_SHOW_DEBUG}
									<td width="20%" style="vertical-align: top">
{if $SHOW_DESIGN_BUTTON}

										Debug Level:
										<SELECT class="swRunMode" name="debug_mode">';
											<OPTION {$DEBUG_NONE} label="None" value="0">None</OPTION>
											<OPTION {$DEBUG_LOW} label="Low" value="1">Low</OPTION>
											<OPTION {$DEBUG_MEDIUM} label="Medium" value="2">Medium</OPTION>
											<OPTION {$DEBUG_HIGH} label="High" value="3">High</OPTION>
										</SELECT>
{/if}
										<BR>
									</td>
{/if}
								</TR>
{else}
{/if}
	</TABLE>
	<TABLE class="swPrpCritBox" cellpadding="0">
<!---->
		<TR id="swPrpCriteriaBody">
			<TD class="swPrpCritEntry">
			<div id="swPrpSubmitPane">
    				<input type="submit" id="prepareAjaxExecute" name="submitPrepare" value="Go">
    				<input type="submit" name="prepareAjaxExpand" value="Reset">
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
                            <input class="swPrpCritExpandButton" id="prepareAjaxButton" type="button" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value=">>">
{else}
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value=">>">
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
    <input type="submit" id="prepareAjaxExecute" name="submitPrepare" value="Go">
    <input type="submit" name="prepareAjaxExpand" value="Reset">
</div>
{/if}
{/if}
			</td>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD id="swPrpExpandCell" rowspan="0" valign="top">
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
							Search {$EXPANDED_TITLE} :<br><input  type="text" name="expand_value" size="30" value="{$EXPANDED_SEARCH_VALUE}"</input>
									<input id="prepareAjaxButton" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br>

{$CONTENT}
							<br>
							<input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="Select All">
							<input class="swPrpSubmit" type="submit" name="EXPANDOK_{$EXPANDED_ITEM}" value="OK">
{/if}
{if !$SHOW_EXPANDED}
{if !$REPORT_DESCRIPTION}
						&nbsp<br>
						Enter Your Report Criteria Here. To enter criteria use the appropriate expand key.
						When you are happy select the appropriate output format and click OK.
{else}
						&nbsp<br>
						{$REPORT_DESCRIPTION}
{/if}
{/if}
{/if}
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
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico {$REPORTICO_VERSION}</a></div>
{if !$EMBEDDED_REPORT} 
</BODY>
</HTML>
{/if}

{if $AJAX_ENABLED} 
{literal}
<script>
jQuery(function($) { 
$(document).ready(function(){
	$(function() {
		$(".swDateField").each(function(){
                                        $(this).datepicker({dateFormat: "{/literal}{$AJAX_DATEPICKER_FORMAT}{literal}"});
                              });

	});
	$('#prepareAjaxExpand').live('click', function() {
       		$("#swPrpExpandCell").addClass("loading");
		var ajaxaction = "{/literal}{$AJAX_PARTIAL_RUNNER}{literal}";
		
       		$.ajax({
           			type: 'POST',
            		url: ajaxaction,
            		data: $("#criteriaform").serialize() + '&partial_template=critbody&execute_mode=PREPARE&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
       					$("#swPrpExpandCell").removeClass("loading");
                   			$("#criteriabody").attr('innerHTML',data);
           			},
           			error: function(xhr, desc, err) {
       					$("#swPrpExpandCell").removeClass("loading");
       					$("#criteriabody").attr('innerHTML','Error in lookup option');
          			}
       		});
		return false;
	});
	$('#prepareAjaxButton').live('click', function() {
       		$("#swPrpExpandCell").addClass("loading");
		var ajaxaction = "{/literal}{$AJAX_PARTIAL_RUNNER}{literal}";
       		$.ajax({
           			type: 'POST',
            		url: ajaxaction,
            		data: $("#criteriaform").serialize() + '&partial_template=expand&execute_mode=PREPARE&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
       					$("#swPrpExpandCell").removeClass("loading");
                   			$("#swPrpExpandCell").attr('innerHTML',data);
           			},
           			error: function(xhr, desc, err) {
       					$("#swPrpExpandCell").removeClass("loading");
       					$("#swPrpExpandCell").attr('innerHTML','Error in lookup option');
          			}
       		});
		return false;
	});
	$('#ignoreprepareAjaxExecute').click(function() {
		//$("#swPrpExpandCell").attr('innerHTML',"Loading");
		var ajaxaction = "{/literal}{$AJAX_PARTIAL_RUNNER}{literal}";
       		$("#swPrpExpandCell").addClass("loading");
            		url: ajaxaction,
       		$.ajax({
           			type: 'POST',
            		url: $("#criteriaform").attr('action'),
            		data: $("#criteriaform").serialize() + '&' + $(this).attr('name') + '=' + $(this).attr('value'),
           			dataType: 'html',
           			success: function(data, status) {
       					$("#swPrpExpandCell").removeClass("loading");
                   			$("#swPrpExpandCell").attr('innerHTML',data);
           			},
           			error: function(xhr, desc, err) {
       					$("#swPrpExpandCell").removeClass("loading");
       					$("#swPrpExpandCell").attr('innerHTML','Error in lookup option');
          			}
       		});
		return false;
	});
});
});
</script>
{/literal}
{/if}
