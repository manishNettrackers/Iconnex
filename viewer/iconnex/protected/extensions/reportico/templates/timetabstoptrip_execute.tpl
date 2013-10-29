{if !$EMBEDDED_REPORT}
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
</HEAD>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
<script type="text/javascript" src="css/tabmod.js"></script>
<BODY class="swRepBody">
{else}
<script type="text/javascript" src="css/tabmod.js"></script>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
{/if}
<FORM id="topmenu" name="topmenu" method="POST" onSubmit='respondToExecuteClick(this); return false;' target='_blank' action="/infohostpd/reportico/swsite/site/inforep/runedit.php?xmlin=timetabmod.xml&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=infohost&MANUAL_date=TODAY&MANUAL_runningno=5044&operation=1&tripId=12345&template=">
{$CONTENT}
</FORM>
{if !$EMBEDDED_REPORT}
</BODY>
</HTML>
{/if}
