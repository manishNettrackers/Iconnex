var lastStopAction = "";

function submit_timetabstop(x) {
    var tag;
    var str;

    resultsdiv = "";
    extrabit = "";

    pressed = window.clicked;
    par = pressed.split("_");
    type = par[0];
    trip = par[1];

    if ( type == "apply" )
    {
        extrabit = "&oper=edit&id=1";
        resultsdiv = "#resultsmodtrip_" + trip;
    }
    if ( type == "deletetrip" )
    {
        extrabit = "&oper=delete&id=1";
        resultsdiv = "#resultsdeltrip_" + trip;
    }

    formvalues = "dummy=dummy";
    for (i=0; i < x.elements.length; i++)
    {
        el = x.elements[i].name;
        str = new String (el);
        ar = str.split("_");

        if ( ar[0] == "deletetrip" )
            continue;
        if ( ar[0] == "apply" )
            continue;
        if ( ar[1] != trip )
            continue;

        if ( ar[0] == "modarr" )
        {
            formvalues = formvalues + "&" + el + "=" + x.elements[i].value;
        }
        if ( ar[0] == "moddep" )
        {
            formvalues = formvalues + "&" + el + "=" + x.elements[i].value;
        }
    }      
    //tag = "#" + x.id;
    //alert((tag).serialize());

    formvalues = formvalues + extrabit;
	$(resultsdiv).html("&nbsp;"); 	// Place space in loading div so its big enough to show image
	$(resultsdiv).addClass("cellloading");
	jQuery.ajax({
		type: "POST",
		url: x.action,
		dataType:"json",
		data:formvalues,
		success:function(response){
		    $(resultsdiv).html("");
			$(resultsdiv).removeClass("cellloading");
		},
		error:function (xhr, ajaxOptions, thrownError){
			//$(resultsdiv).removeClass("cellloading");
			$(resultsdiv).html("Error!");
    		try {
        		resp = JSON.parse(xhr.responseText);
				alert ( "Error: " + resp.errstat + " - " + resp.msgtext );
				
    		} catch (e) 
			{
				alert("Badly formatted response from server" + xhr.responseText);
        		return false;
    		}
		}    
       });
    //$.post(x.action, formvalues, function(html) {
        //$(resultsdiv).html(html);
    //});
    return false;
}

function submit_stopmessage($x) {
    var tag;
    var str;

    $resultsdiv = "";
    $extrabit = "";
    $d = window.clicked;
    for (i=0; i < $x.elements.length; i++)
    {
        $el = $x.elements[i].name;
        $str = new String ($el);
        $ar = $str.split("_");

        if ( $ar[0] == "deletetrip" && window.clicked == "Delete Trip" )
        {
            $extrabit = "&" + $el + "=1";
            $resultsdiv = "#resultsdeltrip_" + $ar[1];
            break;
        }
        if ( $ar[0] == "apply" && window.clicked == "Apply Trip" )
        {
            $extrabit = "&" + $el + "=1";
            $resultsdiv = "#resultsmodtrip_" + $ar[1];
            break;
        }
        $bit = $x
    }      

    $extrabit = "execute_mode=EXECUTE&target_format=HTML&target_show_body=1&";

    $links = "/swsite/site/inforep/timetabstop.php?xmlin=stopmessages.xml&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=rti";
    $resultsdiv = "#ttbresults";
    if ( $d == "EXPAND_operator" || $d == "EXPAND_location" || $d == "EXPAND_route" || $d == "EXPAND_msgtext" || $d == "clearform" )
    {
        $links = "/swsite/site/inforep/timetabstop.php?xmlin=stopmessages.xml&" + $d + "=1";
        $resultsdiv = "#criteria";
    }

    $tag = "#" + $x.id;
    $formvalues = $extrabit + $($tag).serialize();
    $formvalues = $($tag).serialize();
    
    $.post($links, $formvalues, function(html) {
        //alert(html);
        //$("#peter1").html(html);
        $($resultsdiv).html(html);
    });
    return false;
}

//document.groupheadermenu.submit(function() {
    //$x = document.groupheadermenu;
    //$("groupheadermenu").post($(this).attr("action"), $(this).serialize(), function(html) {
        //$("#someDiv").html(html);
    //});
    //return false; // prevent normal submit
//});

function respondToExecuteClick($x) {
    var tag;
    var str;

    $resultsdiv = "";
    $extrabit = "";

    $pressed = window.clicked;
    $par = $pressed.split("_");
    $type = $par[0];
    $trip = $par[1];

    if ( $type == "apply" )
    {
        $extrabit = "&" + $pressed + "=1";
        $resultsdiv = "#resultsmodtrip_" + $trip;
    }
    if ( $type == "deletetrip" )
    {
        $extrabit = "&" + $pressed + "=1";
        $resultsdiv = "#resultsdeltrip_" + $trip;
    }


    $formvalues = "dummy=dummy";
    for (i=0; i < $x.elements.length; i++)
    {
        $el = $x.elements[i].name;
        $str = new String ($el);
        $ar = $str.split("_");

        if ( $ar[0] == "deletetrip" )
            continue;
        if ( $ar[0] == "apply" )
            continue;
        if ( $ar[1] != $trip )
            continue;

        if ( $ar[0] == "modarr" )
        {
            $formvalues = $formvalues + "&" + $el + "=" + $x.elements[i].value;
        }
        if ( $ar[0] == "moddep" )
        {
            $formvalues = $formvalues + "&" + $el + "=" + $x.elements[i].value;
        }
    }      
    //$tag = "#" + $x.id;
    //alert($($tag).serialize());
    $formvalues = $formvalues + $extrabit;
    
        //$links = "/swsite/site/inforep/timetabstop.php?xmlin=stopmessages.xml&" + $d + "=1";
    //$.post("http://10.0.0.9/infohostpd/reportico//swsite/site/inforep/timetabstop.php", $formvalues, function(html) {
    $.post($x.action, $formvalues, function(html) {
        $($resultsdiv).html(html);
        //$("#resultsdeltrip_829112").html(html);
    });
    return false;
}

function updatedep(which)
{
    document.getElementById("depresults").innerHTML = '<iframe id="depresobj" name="depresobj" type="text/html" width=100% height=5000 style="border: 0; overflow: hidden;" onload="ondepresjobjload()" src="'+which.href+'"></iframe>';
}


//
// Ensures all date fields are enabled with date picker functionality
function setDatePickers()
{
    $('.swDateField').datepicker({dateFormat: 'dd/mm/yy'});
    $('.swDateField').css('width', '100px');
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
                $("#criteria_msgline4").css("display", "none");
                $("#criteria_msgline5").css("display", "none");
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

        var y = document.getElementsByName('msgconflict');
        if ( y.length == 0 )
            return false;

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
                $("#criteria_msgline4").css("display", "table-row");
                $("#criteria_msgline5").css("display", "table-row");
                //$("#criteria_sign_type").css("display", "table-row");
                $("#criteria_modgroup").css("display", "table-row");
                $("#criteria_scroll3").css("display", "table-row");
                $("#criteria_infoline1").css("display", "table-row");
                $("#criteria_infoline2").css("display", "table-row");
                $("#criteria_infoline3").css("display", "table-row");
                $("#criteria_infoline4").css("display", "table-row");
                $("#criteria_infoline5").css("display", "table-row");
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
                $("#criteria_msgline4").css("display", "table-row");
                $("#criteria_msgline5").css("display", "table-row");
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
                document.getElementsByName('MANUAL_msgline4')[0].value = '';
                document.getElementsByName('MANUAL_msgline5')[0].value = '';
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
                document.getElementsByName('MANUAL_msgline4')[0].value = '';
                document.getElementsByName('MANUAL_msgline5')[0].value = '';
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

function initstopmessage()
{
        // User Click Report Action Button
        if ( !lastStopAction )
            lastStopaction = "reportmessage";
        reportAction(lastStopAction);
        $("li.stopmessage").click(function(e){  
            switch(e.target.id){  
                case "changemessage":  
                case "sendmessage":  
                case "clearmessage":  
                case "reportmessage":  
                    lastStopAction = e.target.id;
                    reportAction(e.target.id);
                    return false;
                default:
                    reportAction(lastStopAction);
                    return false;
            }  
        return true;  
		});
}
