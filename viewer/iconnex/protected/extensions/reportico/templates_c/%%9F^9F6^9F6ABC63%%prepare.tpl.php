<?php /* Smarty version 2.6.26, created on 2012-12-28 17:35:08
         compiled from prepare.tpl */ ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
<HTML>
<HEAD>
<TITLE><?php echo $this->_tpl_vars['TITLE']; ?>
</TITLE>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<BODY class="swPrpBody">
<?php else: ?>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php endif; ?>

<?php echo '
<!--[if IE]>
<style type="text/css">
    .swPrpTextField
    {
        width: 350px;
    }
</style>
<![endif]-->
'; ?>


<?php if ($this->_tpl_vars['AJAX_ENABLED']): ?> 
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/jquery.ui.datepicker.js"></script>
<!--LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/themes/base/jquery.ui.all.css"-->
<?php endif; ?>

<FORM class="swPrpForm" id="criteriaform" name="topmenu" method="POST" action="<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
">
<h1 class="swTitle"><?php echo $this->_tpl_vars['TITLE']; ?>
</h1>
<input type="hidden" name="session_name" value="<?php echo $this->_tpl_vars['SESSION_ID']; ?>
" />
<?php if ($this->_tpl_vars['SHOW_TOPMENU']): ?>
	<TABLE class="swPrpTopMenu">
		<TR>
<?php if (( $this->_tpl_vars['DB_LOGGEDON'] )): ?> 
			<TD style="width: 10px" class="swPrpTopMenuCell">
			</TD>
<?php endif; ?>
			<TD style="text-align:left">
<?php if ($this->_tpl_vars['SHOW_ADMIN_BUTTON']): ?>
<?php if (strlen ( $this->_tpl_vars['ADMIN_MENU_URL'] ) > 0): ?> 
                <a class="swLinkMenu" href="<?php echo $this->_tpl_vars['ADMIN_MENU_URL']; ?>
"><?php echo $this->_tpl_vars['T_ADMIN_MENU']; ?>
</a>
<?php endif; ?>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?> 
<?php if ($this->_tpl_vars['SHOW_PROJECT_MENU_BUTTON']): ?>
				<a class="swLinkMenu" href="<?php echo $this->_tpl_vars['MAIN_MENU_URL']; ?>
"><?php echo $this->_tpl_vars['T_PROJECT_MENU']; ?>
</a>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_DESIGN_BUTTON']): ?>
                                &nbsp;<input class="swLinkMenu" type="submit" name="submit_design_mode" value="<?php echo $this->_tpl_vars['T_DESIGN_REPORT']; ?>
">
<?php endif; ?>

<?php endif; ?>
			</TD>
<?php if ($this->_tpl_vars['SHOW_LOGOUT']): ?>
			<TD style="width:15%; text-align: right; padding-right: 10px;" class="swPrpTopMenuCell">
				<input class="swLinkMenu" type="submit" name="logout" value="<?php echo $this->_tpl_vars['T_LOGOFF']; ?>
">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
<?php if (strlen ( $this->_tpl_vars['PROJ_PASSWORD_ERROR'] ) > 0): ?>
                                <div style="color: #ff0000;"><?php echo $this->_tpl_vars['T_PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
				<?php echo $this->_tpl_vars['T_ENTER_PROJECT_PASSWORD']; ?>
<br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="<?php echo $this->_tpl_vars['T_LOGIN']; ?>
">
			</TD>
<?php endif; ?>
		</TR>
	</TABLE>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_CRITERIA']): ?>
	<TABLE class="swPrpCritBox" id="critbody" cellpadding="0">
<?php if ($this->_tpl_vars['SHOW_OUTPUT']): ?>
								<TR>
									<td width="10%">
										&nbsp;
									</TD>
									<TD width="40%">
										&nbsp;
										<?php echo $this->_tpl_vars['T_OUTPUT']; ?>

											<INPUT type="radio" name="target_format" value="HTML" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][0]; ?>
>HTML
											<INPUT type="radio" name="target_format" value="PDF" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][1]; ?>
>PDF
											<INPUT type="radio" name="target_format" value="CSV" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][2]; ?>
>CSV
<?php if ($this->_tpl_vars['SHOW_DESIGN_BUTTON']): ?>
											<INPUT type="radio" name="target_format" value="XML" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][3]; ?>
>XML
											<INPUT type="radio" name="target_format" value="JSON" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][4]; ?>
>JSON
											<INPUT type="radio" name="target_format" value="jquerygrid" <?php echo $this->_tpl_vars['OUTPUT_TYPES'][4]; ?>
>JQGRID
<?php endif; ?>
									<td width="30%" style="vertical-align: top">
                                        <?php echo $this->_tpl_vars['T_SHOW']; ?>
<BR>
										<!--INPUT type="checkbox" name="target_attachment" value="1" <?php echo $this->_tpl_vars['OUTPUT_ATTACH']; ?>
>As Attachment</INPUT-->
										<INPUT type="checkbox" name="target_show_criteria" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWCRITERIA']; ?>
><?php echo $this->_tpl_vars['T_SHOW_CRITERIA']; ?>
</INPUT>
										<INPUT type="checkbox" name="target_show_group_headers" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWGROUPHEADERS']; ?>
><?php echo $this->_tpl_vars['T_SHOW_GRPHEADERS']; ?>
</INPUT>
										<INPUT type="checkbox" name="target_show_detail" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWDETAIL']; ?>
><?php echo $this->_tpl_vars['T_SHOW_DETAIL']; ?>
</INPUT>
                                        <BR>
										<INPUT type="checkbox" name="target_show_group_trailers" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWGROUPTRAILERS']; ?>
><?php echo $this->_tpl_vars['T_SHOW_GRPTRAILERS']; ?>
</INPUT>
										<INPUT type="checkbox" name="target_show_column_headers" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWCOLHEADERS']; ?>
><?php echo $this->_tpl_vars['T_SHOW_COLHEADERS']; ?>
</INPUT>
<?php if ($this->_tpl_vars['OUTPUT_SHOW_SHOWGRAPH']): ?>
										<INPUT type="checkbox" name="target_show_graph" value="1" <?php echo $this->_tpl_vars['OUTPUT_SHOWGRAPH']; ?>
><?php echo $this->_tpl_vars['T_SHOW_GRAPH']; ?>
</INPUT><BR>
<?php endif; ?>
									</td>
<?php if ($this->_tpl_vars['OUTPUT_SHOW_DEBUG']): ?>
									<td width="20%" style="vertical-align: top">
<?php if ($this->_tpl_vars['SHOW_DESIGN_BUTTON']): ?>

										<?php echo $this->_tpl_vars['T_DEBUG_LEVEL']; ?>

										<SELECT class="swRunMode" name="debug_mode">';
											<OPTION <?php echo $this->_tpl_vars['DEBUG_NONE']; ?>
 label="None" value="0"><?php echo $this->_tpl_vars['T_DEBUG_NONE']; ?>
</OPTION>
											<OPTION <?php echo $this->_tpl_vars['DEBUG_LOW']; ?>
 label="Low" value="1"><?php echo $this->_tpl_vars['T_DEBUG_LOW']; ?>
</OPTION>
											<OPTION <?php echo $this->_tpl_vars['DEBUG_MEDIUM']; ?>
 label="Medium" value="2"><?php echo $this->_tpl_vars['T_DEBUG_MEDIUM']; ?>
</OPTION>
											<OPTION <?php echo $this->_tpl_vars['DEBUG_HIGH']; ?>
 label="High" value="3"><?php echo $this->_tpl_vars['T_DEBUG_HIGH']; ?>
</OPTION>
										</SELECT>
<?php endif; ?>
										<BR>
									</td>
<?php endif; ?>
								</TR>
<?php else: ?>
<?php endif; ?>
	</TABLE>
<div id="criteriabody">
	<TABLE class="swPrpCritBox" cellpadding="0">
<!---->
		<TR id="swPrpCriteriaBody">
			<TD class="swPrpCritEntry">
			<div id="swPrpSubmitPane">
    				<input type="submit" id="prepareAjaxExecute" name="submitPrepare" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
    				<input type="submit" name="clearform" value="<?php echo $this->_tpl_vars['T_RESET']; ?>
">
			</div>

                <TABLE class="swPrpCritEntryBox"">
<?php if (isset ( $this->_tpl_vars['CRITERIA_ITEMS'] )): ?>
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
<?php if ($this->_tpl_vars['AJAX_ENABLED']): ?> 
                            <input class="swPrpCritExpandButton" id="prepareAjaxButton" type="button" name="EXPAND_<?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['name']; ?>
" value="<?php echo $this->_tpl_vars['T_EXPAND']; ?>
">
<?php else: ?>
                            <input class="swPrpCritExpandButton" type="submit" name="EXPAND_<?php echo $this->_tpl_vars['CRITERIA_ITEMS'][$this->_sections['critno']['index']]['name']; ?>
" value="<?php echo $this->_tpl_vars['T_EXPAND']; ?>
">
<?php endif; ?>
<?php endif; ?>
                        </td>
                    </TR>
<?php endfor; endif; ?>
<?php endif; ?>
                </TABLE>
<?php if (isset ( $this->_tpl_vars['CRITERIA_ITEMS'] )): ?>
<?php if (count ( $this->_tpl_vars['CRITERIA_ITEMS'] ) > 1): ?>
<div id="swPrpSubmitPane">
    <input type="submit" id="prepareAjaxExecute" name="submitPrepare" value="<?php echo $this->_tpl_vars['T_GO']; ?>
">
    <input type="submit" name="clearform" value="<?php echo $this->_tpl_vars['T_RESET']; ?>
">
</div>
<?php endif; ?>
<?php endif; ?>
			</td>
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
<div style="float:right; ">
<?php if (strlen ( $this->_tpl_vars['MAIN_MENU_URL'] ) > 0): ?>
<!--a class="swLinkMenu" style="float:left;" href="<?php echo $this->_tpl_vars['MAIN_MENU_URL']; ?>
">&lt;&lt; Menu</a-->
<?php endif; ?>
</div>
<p>
<?php if ($this->_tpl_vars['SHOW_EXPANDED']): ?>
							<?php echo $this->_tpl_vars['T_SEARCH']; ?>
 <?php echo $this->_tpl_vars['EXPANDED_TITLE']; ?>
 :<br><input  type="text" name="expand_value" size="30" value="<?php echo $this->_tpl_vars['EXPANDED_SEARCH_VALUE']; ?>
"</input>
									<input id="prepareAjaxButton" class="swPrpSubmit" type="submit" name="EXPANDSEARCH_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="Search"><br>

<?php echo $this->_tpl_vars['CONTENT']; ?>

							<br>
							<input class="swPrpSubmit" type="submit" name="EXPANDCLEAR_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="Clear">
							<input class="swPrpSubmit" type="submit" name="EXPANDSELECTALL_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="Select All">
							<input class="swPrpSubmit" type="submit" name="EXPANDOK_<?php echo $this->_tpl_vars['EXPANDED_ITEM']; ?>
" value="OK">
<?php endif; ?>
<?php if (! $this->_tpl_vars['SHOW_EXPANDED']): ?>
<?php if (! $this->_tpl_vars['REPORT_DESCRIPTION']): ?>
<?php echo $this->_tpl_vars['T_DEFAULT_REPORT_DESCRIPTION']; ?>

<?php else: ?>
						&nbsp<br>
						<?php echo $this->_tpl_vars['REPORT_DESCRIPTION']; ?>

<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
			</TABLE>

<?php endif; ?>
</div>
			<!---->

	</TABLE>
</FORM>
<div class="smallbanner">Powered by <a href="http://www.reportico.org/" target="_blank">reportico <?php echo $this->_tpl_vars['REPORTICO_VERSION']; ?>
</a></div>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?> 
</BODY>
</HTML>
<?php endif; ?>

<?php if ($this->_tpl_vars['AJAX_ENABLED']): ?> 
<?php echo '
<script type="text/javascript" src="'; ?>
<?php echo $this->_tpl_vars['JSPATH']; ?>
<?php echo '/ui/i18n/jquery.ui.datepicker-'; ?>
<?php echo $this->_tpl_vars['AJAX_DATEPICKER_LANGUAGE']; ?>
<?php echo '.js"></script>
<script>
jQuery(function($) { 
$(document).ready(function(){
  function setupDatePickers()
  {
    $(".swDateField").each(function(){
    $(this).datepicker({dateFormat: "'; ?>
<?php echo $this->_tpl_vars['AJAX_DATEPICKER_FORMAT']; ?>
<?php echo '"});
    });
  }

  setupDatePickers();

  $(\'#prepareAjaxExpand\').live(\'click\', function() {
    $("#swPrpExpandCell").addClass("loading");
    var ajaxaction = "'; ?>
<?php echo $this->_tpl_vars['AJAX_PARTIAL_RUNNER']; ?>
<?php echo '";
		
    $.ajax({
      type: \'POST\',
      url: ajaxaction,
      data: $("#criteriaform").serialize() + \'&partial_template=critbody&execute_mode=PREPARE&\' + $(this).attr(\'name\') + \'=\' + $(this).attr(\'value\'),
      dataType: \'html\',
      success: function(data, status) {
        $("#swPrpExpandCell").removeClass("loading");
        $("#criteriabody").attr(\'innerHTML\',data);
        setupDatePickers();
        },
        error: function(xhr, desc, err) {
        $("#swPrpExpandCell").removeClass("loading");
        $("#criteriabody").attr(\'innerHTML\',\'Error in lookup option\');
      }
    });
    return false;
	});
  $(\'#prepareAjaxButton\').live(\'click\', function() {
    $("#swPrpExpandCell").addClass("loading");
    var ajaxaction = "'; ?>
<?php echo $this->_tpl_vars['AJAX_PARTIAL_RUNNER']; ?>
<?php echo '";
      $.ajax({
        type: \'POST\',
        url: ajaxaction,
        data: $("#criteriaform").serialize() + \'&partial_template=expand&execute_mode=PREPARE&\' + $(this).attr(\'name\') + \'=\' + $(this).attr(\'value\'),
        dataType: \'html\',
        success: function(data, status) {
          $("#swPrpExpandCell").removeClass("loading");
          $("#swPrpExpandCell").attr(\'innerHTML\',data);
        },
        error: function(xhr, desc, err) {
          $("#swPrpExpandCell").removeClass("loading");
          $("#swPrpExpandCell").attr(\'innerHTML\',\'Error in lookup option\');
        }
      });
      return false;
    });
  });
});
</script>
'; ?>

<?php endif; ?>