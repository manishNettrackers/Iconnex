<?php /* Smarty version 2.6.26, created on 2012-12-04 21:02:44
         compiled from pwi_prepare.tpl */ ?>
<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="/iconnex/yii/iconnex/protected/extensions/reportico/stylesheet/pwi.css"-->

<?php echo '
<STYLE type="text/css">
.swPrpDropSelect
{
    font-size: 8pt;
}
.sw1PrpCritExpandButton, .sw1PrpSidePanelCritExpandButton
{
	text-align: left;
    background-color: #fff;
    background-image: url(\'/iconnex/yii/iconnex/css/lookup.png\');
    background-position:  center center;
    background-repeat: no-repeat;
    margin-left: 2px;
    padding: 0; color: inherit; border: 0px solid #D09C99; width: 16px; height: 16px; margin: 1px 0px 4px 0px; text-decoration: none;
}

</STYLE>
'; ?>


<FORM class="swPrpForm" id="critform" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<input style="display:inline" type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" />
<div id="pwierror" style="width:100%; display: inline">
&nbsp;
</div>
<?php if ($this->_tpl_vars['SHOW_CRITERIA']): ?>
<?php if (! $this->_tpl_vars['SHOW_EXPANDED']): ?>
<!--div id='maintitle' ><?php echo $this->_tpl_vars['TITLE']; ?>
</div-->
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
<?php endif; ?>

<?php if ($this->_tpl_vars['SHOW_EXPANDED']): ?>
			<TABLE>
			<TR>
			<TD class="swPrpExpand">
				<TABLE class="swPrpExpandBox">
					<TR class="swPrpExpandRow">
						<TD id="swPrpExpandCell" rowspan="0" valign="top">
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
<?php if (strlen ( $this->_tpl_vars['STATUSMSG'] ) == 0 && strlen ( $this->_tpl_vars['ERRORMSG'] ) == 0): ?>
</div>
<?php if ($this->_tpl_vars['SHOW_EXPANDED']): ?>
							<input class="swPrpReturnFromExpand" type="button" name="EXPANDOK_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="<<">
							<!--Search <?php echo $this->_tpl_vars['EXPANDED_TITLE']; ?>
: <input  type="text" class="swPrpSearchBox" name="expand_value" size="8" value="<?php echo $this->_tpl_vars['EXPANDED_SEARCH_VALUE']; ?>
"</input-->
									<!--input id="prepareAjaxButton" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="Search"><br-->

<?php echo $this->_tpl_vars['CONTENT']; ?>

							<br>
							<!--input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="All"-->
<?php endif; ?>
<?php endif; ?>
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>
<?php endif; ?>
<p>&nbsp;
<p>&nbsp;
<div id="swPrpCriteriaBody" style="width: 100%; display: block;">
<?php unset($this->_sections['critno']);
$this->_sections['critno']['name'] = 'critno';
$this->_sections['critno']['loop'] = is_array($_loop=$this->_tpl_vars['CRITERIA_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['critno']['show'] = true;
$this->_sections['critno']['max'] = $this->_sections['critno']['loop'];
$this->_sections['critno']['step'] = 1;
$this->_sections['critno']['start'] = $this->_sections['critno']['step'] > 0 ? 0 : $this->_sections['critno']['loop']-1;
if ($this->_sections['critno']['show']) {
    $this->_sections['critno']['total'] = $this->_sections['critno']['loop'];
    if ($this->_sections['critno']['total'] == 0)
        $this->_sections['critno']['show'] = false;
} else
    $this->_sections['critno']['total'] = 0;
if ($this->_sections['critno']['show']):

            for ($this->_sections['critno']['index'] = $this->_sections['critno']['start'], $this->_sections['critno']['iteration'] = 1;
                 $this->_sections['critno']['iteration'] <= $this->_sections['critno']['total'];
                 $this->_sections['critno']['index'] += $this->_sections['critno']['step'], $this->_sections['critno']['iteration']++):
$this->_sections['critno']['rownum'] = $this->_sections['critno']['iteration'];
$this->_sections['critno']['index_prev'] = $this->_sections['critno']['index'] - $this->_sections['critno']['step'];
$this->_sections['critno']['index_next'] = $this->_sections['critno']['index'] + $this->_sections['critno']['step'];
$this->_sections['critno']['first']      = ($this->_sections['critno']['iteration'] == 1);
$this->_sections['critno']['last']       = ($this->_sections['critno']['iteration'] == $this->_sections['critno']['total']);
?>
<?php if ($this->_tpl_vars['SHOW_EXPANDED']): ?>
                <TABLE class="swPrpCritEntryBox" style="display:none">
<?php else: ?>
                <TABLE class="swPrpCritEntryBox">
<?php endif; ?>
<?php unset($this->_sections['critno']);
$this->_sections['critno']['name'] = 'critno';
$this->_sections['critno']['loop'] = is_array($_loop=$this->_tpl_vars['CRITERIA_ITEMS']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['critno']['show'] = true;
$this->_sections['critno']['max'] = $this->_sections['critno']['loop'];
$this->_sections['critno']['step'] = 1;
$this->_sections['critno']['start'] = $this->_sections['critno']['step'] > 0 ? 0 : $this->_sections['critno']['loop']-1;
if ($this->_sections['critno']['show']) {
    $this->_sections['critno']['total'] = $this->_sections['critno']['loop'];
    if ($this->_sections['critno']['total'] == 0)
        $this->_sections['critno']['show'] = false;
} else
    $this->_sections['critno']['total'] = 0;
if ($this->_sections['critno']['show']):

            for ($this->_sections['critno']['index'] = $this->_sections['critno']['start'], $this->_sections['critno']['iteration'] = 1;
                 $this->_sections['critno']['iteration'] <= $this->_sections['critno']['total'];
                 $this->_sections['critno']['index'] += $this->_sections['critno']['step'], $this->_sections['critno']['iteration']++):
$this->_sections['critno']['rownum'] = $this->_sections['critno']['iteration'];
$this->_sections['critno']['index_prev'] = $this->_sections['critno']['index'] - $this->_sections['critno']['step'];
$this->_sections['critno']['index_next'] = $this->_sections['critno']['index'] + $this->_sections['critno']['step'];
$this->_sections['critno']['first']      = ($this->_sections['critno']['iteration'] == 1);
$this->_sections['critno']['last']       = ($this->_sections['critno']['iteration'] == $this->_sections['critno']['total']);
?>
                    <tr class="swPrpCritLine" id="criteria_<?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['name']; ?>
">
                        <td class='swPrpCritTitle'>
                            <?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['title']; ?>

                        </td>
                        <td class="swPrpCritSel">
                            <?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['entry']; ?>

                        </td>
                        <td class="swPrpCritExpandSel">
<?php if ($this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['expand']): ?>
                            <input class="swPrpSidePanelCritExpandButton" type="button" name="EXPAND_<?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['name']; ?>
" value="">
<?php endif; ?>
                        </td>
                    </TR>
<?php endfor; endif; ?>
                </TABLE>
<?php endfor; endif; ?>

</div>
<?php endif; ?>

</FORM>