    <div id="subwindowframe" class="ui-widget-content curved">
        <div class="content curved">
            <div class="paneheader ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                <p id="subwindowtitle" style="float:left">Details</p>
                <div id="swclose" style="float: right"class="button"><a title="Close" href="javascript:hideSubwindow('subwindow')"><img src="<?php get_app_url(); ?>/images/close.png"></img></a></div>
            </div>
			<div id="subwindow" class="ui-widget-content"></div>
        </div>
    </div>

<script>
    $(function() {
        $( "#subwindowframe" ).draggable({cancel: ".button,#subwindow" });
    });
</script>
