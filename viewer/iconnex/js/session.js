
var sessionParams = [];

function init_session_params ( session, query )
{
        if (!sessionParams[session] )
			sessionParams[session] = [];

		sessionParams[session].lastrefresh = false;
		sessionParams[session].hasmap = false;
		sessionParams[session].hasgrid = true;
		sessionParams[session].hasline = false;
		sessionParams[session].hasreport = false;
		sessionParams[session].haschart = false;
		sessionParams[session].autorefresh = false;
		sessionParams[session].autorecentre = true;
		sessionParams[session].autozoom = true;
		sessionParams[session].originalxml = false;
		sessionParams[session].refreshxml = false;
		sessionParams[session].plottype = "ICON";
		sessionParams[session].title = query;
		sessionParams[session].autorun = false;
		sessionParams[session].show_criteria = true;
		sessionParams[session].map_control = false;
		sessionParams[session].map_layer = false;
		sessionParams[session].runlocation = "SIDEPANEL";
		sessionParams[session].runhtml = false;
		sessionParams[session].chart = false;
		sessionParams[session].current_view_type = false;
		if ( menuitems[query].hasmap )
			sessionParams[session].hasmap = menuitems[query].hasmap;
		if ( menuitems[query].hasgrid )
			sessionParams[session].hasgrid = menuitems[query].hasgrid;
		if ( menuitems[query].hasline )
			sessionParams[session].hasline = menuitems[query].hasline;
		if ( menuitems[query].hasreport )
			sessionParams[session].hasreport = menuitems[query].hasreport;
		if ( menuitems[query].haschart )
			sessionParams[session].haschart = menuitems[query].haschart;
		if ( menuitems[query].autorefresh )
			sessionParams[session].autorefresh = menuitems[query].autorefresh;
		if ( menuitems[query].autorecentre )
			sessionParams[session].autorecentre = menuitems[query].hasmap;
		if ( menuitems[query].autozoom )
			sessionParams[session].autozoom = menuitems[query].hasmap;
		if ( menuitems[query].refreshxml )
			sessionParams[session].refreshxml = menuitems[query].refreshxml;
		if ( menuitems[query].originalxml )
			sessionParams[session].originalxml = menuitems[query].originalxml;
		if ( menuitems[query].runlocation )
			sessionParams[session].runlocation = menuitems[query].runlocation;
		//if ( menuitems[query].plottype )
			//sessionParams[session].plottype = menuitems[query].plottype;
		if ( menuitems[query].autorun )
			sessionParams[session].autorun = menuitems[query].autorun;
		if ( menuitems[query].show_criteria )
			sessionParams[session].show_criteria = 0;
}

function get_session_param (session, option )
{
        if (!sessionParams[session] )
		{
            //if ( !session  )
                //alert ("bad");
			sessionParams[session] = [];
			sessionParams[session].lastrefresh = false;
			sessionParams[session].hasmap = false;
			sessionParams[session].hasgrid = true;
			sessionParams[session].hasline = false;
			sessionParams[session].hasreport = false;
			sessionParams[session].haschart = false;
			sessionParams[session].autorefresh = false;
			sessionParams[session].autorecentre = true;
			sessionParams[session].autozoom = true;
			sessionParams[session].plottype = "ICON";
			sessionParams[session].title = "unknown";
		    sessionParams[session].current_view_type = false;
		}
		return sessionParams[session][option];

		
}
function set_session_param (session, option, value )
{
        if (!sessionParams[session] )
		{
            //if ( !session  )
                //alert ("bad");
			sessionParams[session] = [];
			sessionParams[session].lastrefresh = false;
			sessionParams[session].hasmap = false;
			sessionParams[session].hasgrid = true;
			sessionParams[session].hasline = true;
			sessionParams[session].hasreport = false;
			sessionParams[session].haschart = false;
			sessionParams[session].autorefresh = false;
			sessionParams[session].autorecentre = true;
			sessionParams[session].autozoom = true;
			sessionParams[session].maptype = "ICON";
		}
		sessionParams[session][option] = value;
}
