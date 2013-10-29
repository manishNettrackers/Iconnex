{if !$EMBEDDED_REPORT}
<HTML>
<HEAD>
<TITLE>{$TITLE}</TITLE>
</HEAD>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}">
<BODY class="swRepBody">
{else}
<!--LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="{$STYLESHEET}"-->
{/if}

{literal}
 <STYLE type="text/css">
.cellloading {
    background-image: url('/demo/iconnex/images/loading.gif');
    background-position:  top left;
    background-repeat: no-repeat;
    opacity: 1;
}
.deletebutton {
    background-image: url('/demo/iconnex/images/delete.png');
    background-position:  top center;
    background-repeat: no-repeat;
    width: 20px;
    opacity: 1;
}
.swRepResultLine td input
{
    margin: 0px 0px 0px 0px;
    padding: 2px 2px 2px 2px;
    border: 1px solid #d0ccc9;
    border-width: 1px 1px 1px 1px;
    position: relative;
    color: #505050;
    cursor: pointer;
}
 </STYLE>
{/literal}

<FORM id="reporticoform" name="reporticoform" method="POST" onSubmit='submit_timetabstop(this); return false;' target='_blank' action="protected/extensions/reportico/embedded.php?dbview=timetabmods&xmlin=timetabmod.xml&execute_mode=MODIFY&target_format=HTML&target_show_body=1&project=rti&template=">
{$CONTENT}
</FORM>
{if !$EMBEDDED_REPORT}
</BODY>
</HTML>
{/if}

