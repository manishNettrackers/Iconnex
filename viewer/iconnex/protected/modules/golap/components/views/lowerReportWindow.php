oooooo
<div id="lowerReportWindow" style="clear:none; height: 800px; width: 100%">

    <div id="lowerReportWindowframe" class="curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="windowtitle" style="float:left">Stop Arrivals</p>
                <div id="close" style="float: right" class="button"><a title="Close" href="javascript:hideSubwindow('webstopwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
                <div id="popout" class="button"></div>
            </div>
            <div id="webstopwindow" class="ui-widget-content"></div>
        </div>
    </div>


</div>

<script>
        $(function() {
                $( "#lowerReportWindowframe" ).draggable({cancel: ".button,#lowerReportWindow"});
               });
</script>
