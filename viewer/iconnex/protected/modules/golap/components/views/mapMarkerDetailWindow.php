    <div id="smallsubwindowframe" class="curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="smallsubwindowtitle" style="float:left">Details</p>
                <div id="close" style="float: right" class="button"><a title="Close" href="javascript:hideSubwindow('smallsubwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
                <div id="popout" class="button"></div>
            </div>
            <div id="smallsubwindow" class="ui-widget-content"></div>     
            <div id="messages"></div>
            <div class="panefooter">
            	<div id="dstatus" class="stattxt"></div>
            	<div id="approaching" class="approaching"></div>
            </div>
        </div>
    </div>

    <div id="webstopwindowframe" class="curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="windowtitle" style="float:left">Stop Arrivals</p>
                <div id="close" style="float: right" class="button"><a title="Close" href="javascript:hideSubwindow('webstopwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
                <div id="popout" class="button"></div>
            </div>
            <div id="webstopwindow" class="ui-widget-content"></div>     
            <!--div id="messages"></div>
            <div class="panefooter">
            	<div id="dstatus" class="stattxt"></div>
            	<div id="approaching" class="approaching"></div>
            </div-->
        </div>
    </div>

<script>
        $(function() {
                $( "#smallsubwindowframe" ).draggable({cancel: ".button,#smallsubwindow"});
                $( "#webstopwindowframe" ).draggable({cancel: ".button,#webstopwindow"});
               });
</script>
