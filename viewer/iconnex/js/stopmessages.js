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

var loaded = false;

function postload ()
{
    reportAction(latestAction);
}


function hideAll()
{
                $("#criteria_operator").css("display", "none");
                $("#criteria_location").css("display", "none");
                $("#criteria_route").css("display", "none");
                $("#criteria_fromdate").css("display", "none");
                $("#criteria_todate").css("display", "none");
                $("#criteria_fromtime").css("display", "none");
                $("#criteria_totime").css("display", "none");
                $("#criteria_msgline1").css("display", "none");
                $("#criteria_msgline2").css("display", "none");
                $("#criteria_msgline3").css("display", "none");
                $("#criteria_sign_type").css("display", "none");
                $("#criteria_msgtext").css("display", "none");
                $("#criteria_clearmsg").css("display", "none");
                $("#criteria_modgroup").css("display", "none");
                $("#criteria_msgconflict").css("display", "none");
                $("#criteria_msgname").css("display", "none");
                $("#criteria_scroll3").css("display", "none");
                $("#criteria_infoline1").css("display", "none");
                $("#criteria_infoline2").css("display", "none");
                $("#criteria_infoline3").css("display", "none");
                $("#criteria_tftroute").css("display", "none");
                $("#criteria_tftfull").css("display", "none");
                $("#criteria_actionMode").css("display", "none");
}

function reportAction ( actionMode )
{

		latestAction = actionMode;
        hideAll();
        switch(actionMode){  
            case "changemessage":  
                $("#sendmessage").removeClass("active");  
                $("#changemessage").addClass("active");  
                $("#clearmessage").removeClass("active");  
                $("#reportmessage").removeClass("active");  
                $("#criteria_fromdate").css("display", "table-row");
                $("#criteria_todate").css("display", "table-row");
                $("#criteria_fromtime").css("display", "table-row");
                $("#criteria_totime").css("display", "table-row");
                $("#criteria_msgline1").css("display", "table-row");
                $("#criteria_msgline2").css("display", "table-row");
                $("#criteria_msgline3").css("display", "table-row");
                //$("#criteria_sign_type").css("display", "table-row");
                $("#criteria_modgroup").css("display", "table-row");
                $("#criteria_scroll3").css("display", "table-row");
                $("#criteria_infoline1").css("display", "table-row");
                $("#criteria_infoline2").css("display", "table-row");
                $("#criteria_infoline3").css("display", "table-row");
                $("#criteria_tftroute").css("display", "table-row");
                $("#criteria_tftfull").css("display", "table-row");
                var y = document.getElementsByName('msgconflict');
                $(y)[0].checked = false;
                $(y)[1].checked = false;
                $(y)[2].checked = true;
                var x = document.getElementsByName('clearmsg[]');
                $(x)[0].checked = false;
                document.getElementsByName('MANUAL_msgname')[0].value = '';
                document.getElementsByName('MANUAL_actionMode')[0].value = actionMode;
                return false;
            break;  
            case "sendmessage":  
                $("#changemessage").removeClass("active");  
                $("#sendmessage").addClass("active");  
                $("#clearmessage").removeClass("active");  
                $("#reportmessage").removeClass("active");  
                $("#criteria_operator").css("display", "table-row");
                $("#criteria_location").css("display", "table-row");
                $("#criteria_route").css("display", "table-row");
                $("#criteria_fromdate").css("display", "table-row");
                $("#criteria_todate").css("display", "table-row");
                $("#criteria_fromtime").css("display", "table-row");
                $("#criteria_totime").css("display", "table-row");
                $("#criteria_msgline1").css("display", "table-row");
                $("#criteria_msgline2").css("display", "table-row");
                $("#criteria_msgline3").css("display", "table-row");
                //$("#criteria_sign_type").css("display", "table-row");
                $("#criteria_msgconflict").css("display", "table-row");
                $("#criteria_msgname").css("display", "table-row");
                $("#criteria_scroll3").css("display", "table-row");
                $("#criteria_infoline1").css("display", "table-row");
                $("#criteria_infoline2").css("display", "table-row");
                $("#criteria_infoline3").css("display", "table-row");
                $("#criteria_tftroute").css("display", "table-row");
                $("#criteria_tftfull").css("display", "table-row");
                var y = document.getElementsByName('msgconflict');
                $(y)[0].checked = true;
                $(y)[1].checked = false;
                $(y)[2].checked = false;
                var x = document.getElementsByName('clearmsg[]');
                $(x)[0].checked = false;
                document.getElementsByName('MANUAL_actionMode')[0].value = actionMode;
                return false;
            break;  
            case "clearmessage":  
                $("#changemessage").removeClass("active");  
                $("#clearmessage").addClass("active");  
                $("#sendmessage").removeClass("active");  
                $("#reportmessage").removeClass("active");  
                $("#criteria_clearmsg").css("display", "table-row");
                $("#criteria_modgroup").css("display", "table-row");
                document.getElementsByName('MANUAL_infoline1')[0].value = '';
                document.getElementsByName('MANUAL_infoline2')[0].value = '';
                document.getElementsByName('MANUAL_infoline3')[0].value = '';
                document.getElementsByName('MANUAL_tftroute')[0].value = '';
                document.getElementsByName('MANUAL_tftfull')[0].value = '';
                document.getElementsByName('MANUAL_msgline1')[0].value = '';
                document.getElementsByName('MANUAL_msgline2')[0].value = '';
                document.getElementsByName('MANUAL_msgline3')[0].value = '';
                document.getElementsByName('MANUAL_msgname')[0].value = '';
                var x = document.getElementsByName('clearmsg[]');
                $(x)[0].checked = true;
                document.getElementsByName('MANUAL_actionMode')[0].value = actionMode;
                for(var i = 0; i < document.getElementsByName('modgroup').length; i++)
                    document.getElementsByName('modgroup')[i].checked = false;
                return false;
            break;  
            case "reportmessage":  
                $("#changemessage").removeClass("active");  
                $("#reportmessage").addClass("active");  
                $("#sendmessage").removeClass("active");  
                $("#clearmessage").removeClass("active");  
                $("#criteria_operator").css("display", "table-row");
                $("#criteria_location").css("display", "table-row");
                $("#criteria_route").css("display", "table-row");
                document.getElementsByName('MANUAL_infoline1')[0].value = '';
                document.getElementsByName('MANUAL_infoline2')[0].value = '';
                document.getElementsByName('MANUAL_infoline3')[0].value = '';
                document.getElementsByName('MANUAL_tftroute')[0].value = '';
                document.getElementsByName('MANUAL_tftfull')[0].value = '';
                document.getElementsByName('MANUAL_msgline1')[0].value = '';
                document.getElementsByName('MANUAL_msgline2')[0].value = '';
                document.getElementsByName('MANUAL_msgline3')[0].value = '';
                //document.getElementsByName('MANUAL_msgtext')[0].value = '';
                document.getElementsByName('MANUAL_msgname')[0].value = '';
                var x = document.getElementsByName('clearmsg[]');
                $(x)[0].checked = false;
                document.getElementsByName('MANUAL_actionMode')[0].value = actionMode;
                for(var i = 0; i < document.getElementsByName('modgroup').length; i++)
                    document.getElementsByName('modgroup')[i].checked = false;
                return false;
            break;  
        }
}

$(document).ready(function(){  

        // On document load default to report view
        if ( !loaded )
        {
                
                //document.CriteriaBox.css("display", "inline");
                $("#CriteriaBox").css("display", "inline");

                if ( document.getElementsByName('MANUAL_actionMode')[0].value == "" )
                {
                    document.getElementsByName('MANUAL_actionMode')[0].value = 'reportmessage';
                }
                reportAction(document.getElementsByName('MANUAL_actionMode')[0].value);
                $loaded = true;
        }
        // User Click Report Action Button
        $("li").click(function(e){  
            switch(e.target.id){  
                case "changemessage":  
                case "sendmessage":  
                case "clearmessage":  
                case "reportmessage":  
                    reportAction(e.target.id);
                    return false;
            }  
        return true;  
    });  
});  
