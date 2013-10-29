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

