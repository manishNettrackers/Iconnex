<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="/iconnex/yii/iconnex/protected/extensions/reportico/stylesheet/pwi.css"-->

{literal}
<STYLE type="text/css">
.swPrpDropSelect
{
    font-size: 8pt;
}
.sw1PrpCritExpandButton, .sw1PrpSidePanelCritExpandButton
{
	text-align: left;
    background-color: #fff;
    background-image: url('/iconnex/yii/iconnex/css/lookup.png');
    background-position:  center center;
    background-repeat: no-repeat;
    margin-left: 2px;
    padding: 0; color: inherit; border: 0px solid #D09C99; width: 16px; height: 16px; margin: 1px 0px 4px 0px; text-decoration: none;
}

</STYLE>
{/literal}

<FORM class="swPrpForm" id="critform" name="topmenu" method="POST" action="{$SCRIPT_SELF}">
<input style="display:inline" type="hidden" name="session_name" value="{$SESSION_ID}" />
<div id="pwierror" style="width:100%; display: inline">
&nbsp;
</div>
{if $SHOW_CRITERIA}
{if !$SHOW_EXPANDED}
<!--div id='maintitle' >{$TITLE}</div-->
<div style="width: 100%;display: block;">
<div style="width: 60%; float:left;">
<input type="submit" label="Plot Data" class="swPlotButton" name="submitPrepare" value="">
<input type="submit" label="List Data" class="swDataButton" name="submitPrepareData" value="">
<input type="submit" label="Line View" class="swLineButton" name="submitPrepare" value="">
<input type="submit" label="Report View" class="swReportButton" style="display:none" name="submitPrepareReport" value="">
<input type="submit" label="Chart View" class="swChartButton" style="display:none" name="submitPrepareChart" value="">
&nbsp;
<input type="button" label="Map Filtering" class="showfiltermap" style="display:none" title="Filter Map"/>
</div>
<div class="labelpairs" style="width: 40%;">
<!--div class="labelMapPair"><label>Autozoom</label><input type="checkbox" class="swAutoZoom" name="autorezoom" value=""/></div>
<div class="labelMapPair"><label>Auto-Centre</label><input type="checkbox" class="swAutoCentre" name="autocentre" value=""/></div-->
<div class="labelpair"><label>Refresh</label><input type="checkbox" class="swAutoRefresh" name="autorefresh" value=""/></div>
</div>
</div>
{/if}

{if $SHOW_EXPANDED}
			<TABLE>
			<TR>
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
</div>
{if $SHOW_EXPANDED}
							<input class="swPrpReturnFromExpand" type="button" name="EXPANDOK_{$EXPANDED_ITEM}" value="<<">
							<!--Search {$EXPANDED_TITLE}: <input  type="text" class="swPrpSearchBox" name="expand_value" size="8" value="{$EXPANDED_SEARCH_VALUE}"</input-->
									<!--input id="prepareAjaxButton" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_{$EXPANDED_ITEM}" value="Search"><br-->

{$CONTENT}
							<br>
							<!--input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_{$EXPANDED_ITEM}" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_{$EXPANDED_ITEM}" value="All"-->
{/if}
{/if}
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>
{/if}
<p>&nbsp;
<p>&nbsp;
<div id="swPrpCriteriaBody" style="width: 100%; display: block;">
{section name=critno loop=$CRITERIA_ITEMS}
{if $SHOW_EXPANDED }
                <TABLE class="swPrpCritEntryBox" style="display:none">
{else}
                <TABLE class="swPrpCritEntryBox">
{/if}
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
                            <input class="swPrpSidePanelCritExpandButton" type="button" name="EXPAND_{$CRITERIA_ITEMS[critno].name}" value="">
{/if}
                        </td>
                    </TR>
{/section}
                </TABLE>
{/section}

</div>
{/if}

</FORM>
