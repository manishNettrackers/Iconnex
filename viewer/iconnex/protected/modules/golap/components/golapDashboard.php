<?php
Yii::app()->clientScript->registerScript('golapDashboardEvents',<<<EOD

    var sourceportlet = null;
    var sourcetag = null;

    $(function() {
        $( ".dashboardsortable" ).sortable({
            cursor: 'move', 
            cancel: 'input, .portlet-content',
            connectWith: ".dashboardsortable",
            start: function( event, ui ) { 
                      //ui.item.parents(".portlet")
                      sourceportlet = $(ui.item).parent().attr("id");
                      sourcetag = $(ui.item).attr("id");
                 },
            beforeStop: function( event, ui ) { 
                      //ui.item.parents(".portlet")
                      targetportlet = $(ui.item).attr( "id");
                      sourcesession = sourceportlet.substring(4);
                      sourcesession = sourceportlet.substring(4);
                      //id = $(this).attr("id");
                      //session = $(ui.item).attr( "id").substring(4);;
                      //$("#" + targetportlet).appendTo($("#" + sourceportlet));
                      sizeDashboardGridToFitParent (sourcesession);
                      sizeDashboardMapToFitParent(sourcesession);
                 },
            stop: function( event, ui ) { 
                      id = $(this).attr("id");
                      session = $(ui.item).attr( "id").substring(4);;
                      sizeDashboardGridToFitParent ( session );
                      sizeDashboardMapToFitParent(session);
                 },
            update: function( event, ui ) { 
                // After user has moved a portlet in dashboard, if there was
                // already a portlet there then swap them round. Find this portlet by
                // looking for the moved portlets new siblings
                //ui.item.parents(".portlet")
                compdash = null;
                sib = $("#" + sourcetag).siblings().each(function(){
				    compdash = $(this).attr("id");
				    if ( sourcetag != compdash )
				    {
					    $("#" + gDashboardLayout).find("#" + compdash + ":first").appendTo($("#" + gDashboardLayout).find("#" + sourceportlet));
				    }
			    });

                targetsession = targetportlet.substring(4);
                sizeDashboardGridToFitParent (sourcesession);
                sizeDashboardMapToFitParent(sourcesession);
                if ( compdash != null )
                {
                    sizeDashboardGridToFitParent (compdash.substring(4));
                    sizeDashboardMapToFitParent(compdash.substring(4));
                }
            }
        });
 
        $( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
                .addClass( "ui-widget-header ui-corner-all" )
                .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
                .end()
            .find( ".portlet-content" );
 
        $( ".portlet-header .ui-icon" ).click(function() {
            $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
            $( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
        });
 
        $( ".column" ).disableSelection();
    });

EOD
,CClientScript::POS_READY);

Yii::app()->clientScript->registerCss('golapDashboardStyles',<<<EOD

.dashboardrow { width: 100%; display: block; height: 200px }
.portlet-header { margin: 0.3em; padding-bottom: 1px; padding-left: 0.2em; }
.portlet-header .ui-icon { float: right; }
.portlet-content { padding: 0.0em 0.4em 0.0em 0.4em; }
.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
.ui-sortable-placeholder * { visibility: hidden; }

//.portlet { margin: 0 1em 1em 0; }

.portlet-minimized { height: auto !important; }
.portlet-maximized { width: 100%; height: 100%; }

//.dashboardsortable { float: left; }
.dashboardsortable {  overflow: hidden; padding-bottom: 2px;}
.dashcolumn-25 { width: 24%; height: 100%}
.dashcolumn-75 { width: 74%; height: 100%}
.dashcolumn-30 { width: 30%; height: 100%}
.dashcolumn-100 { width: 100%; clear: both; display: block;  height: 100px}
.dashcolumn-100-50 { width: 100%; height: 50%;  }

.dashcolumn-maximized { width: 100%; height: 100%; display: inline; } 
.dashcolumn-minimized { display:none !important; }

.dashrow-25 { height:25% }
.dashrow-50 { height:50% }
.dashrow-100 { height:100% }
.dashrow-minimized { display:none !important; }
.dashrow-maximized { height: 100%; display: inline; } 
.dash-highlight { border: none 1px #666666; }
.dashcol-highlight { border: dashed 1px #DDDDAA; }

EOD
);

    class golapDashboard extends GolapWidget
    {
        public function init(){
            parent::init();
            Yii::app()->getClientScript()->registerScriptFile($this->_assetsUrl.'/golapDashboard.js');
        }


        public function run(){
            $this->render('golapDashboard');
        }
    }
?>
