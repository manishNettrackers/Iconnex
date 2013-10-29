<?php
// -----------------------------------------------------------------------------
// -- Reportico -----------------------------------------------------------------
// -----------------------------------------------------------------------------
// Module : config.php
//
// General User Configuration Settings for SeekWell Operation
// -----------------------------------------------------------------------------

// Location of SeekWell Top Level Directory From Browser Point of View
define('SW_HTTP_BASEDIR','./');
define('SW_HTTP_APP_DIR','/odspd/viewer/iconnex/');
define('SW_HTTP_REPORTS_BASEDIR',SW_HTTP_APP_DIR.'protected/extensions/reportico/');
define('SW_HTTP_ASSETS_DIR', SW_HTTP_APP_DIR.'assets/');
define('SW_DEFAULT_PROJECT', 'reports');

//Authentication model
define('SW_AUTHENTICATION_MODEL', 'YII');

// Identify whether to always run in into Debug Mode
define('SW_ALLOW_OUTPUT', true);
define('SW_ALLOW_DEBUG', true);

// Identify whether Show Criteria is default option
define('SW_DEFAULT_SHOWCRITERIA', false);

// Specification of Safe Mode. Turn on SAFE mode by specifying true.
// In SAFE mode, design of reports is allowed but Code and SQL Injection
// are prevented. This means that the designer prevents entry of potentially
// cdangerous ustom PHP source in the Custom Source Section or potentially
// dangerous SQL statements in Pre-Execute Criteria sections
define('SW_SAFE_DESIGN_MODE', false);
define('SW_PROJECT', 'rti');

// If false prevents any designing of reports
define('SW_ALLOW_MAINTAIN', true);

//  Stylesheets
define('SW_STYLESHEET','stylesheet/bluetheme.css');
//define('SW_STYLESHEET','stylesheet/bluetheme.css');
//define('SW_STYLESHEET','stylesheet/bluetheme.css');
//define('SW_STYLESHEET','stylesheet/bluetheme.css');

// DB connection details for ADODB
//define('SW_DB_DRIVER','pdo_mysql');
//define('SW_DB_USER','root');
//define('SW_DB_PASSWORD','');
//define('SW_DB_HOST','127.0.0.1');
//define('SW_DB_PASSWORD','');
//define('SW_DB_HOST','127.0.0.1');
//define('SW_DB_DATABASE','infohost');
define('SW_DB_CONNECT_FROM_CONFIG', true);
define('SW_DB_DATEFORMAT','d/m/Y');
define('SW_PREP_DATEFORMAT','d/m/Y');
define('SW_DB_DRIVER',  ICX_RTPI_DB_DRIVER_REPORTICO);
define('SW_DB_USER', ICX_RTPI_DB_USER);
define('SW_DB_PASSWORD', ICX_RTPI_DB_PASSWORD);
define('SW_DB_HOST', ICX_RTPI_DB_HOST);
define('SW_DB_DATABASE', ICX_RTPI_DB_NAME);
define('SW_DB_SERVER', ICX_RTPI_DB_SERVER);
define('SW_DB_PROTOCOL', ICX_RTPI_DB_PROTOCOL);
define('SW_DB_SERVICE', ICX_RTPI_DB_SERVICE);


// Identify whether to use AJAX handling. Enabling with enable Data Pickers,
// loading of partial form elements and quicker-ti-use design mode
define('AJAX_ENABLED',true);


// Identify temp area
define('SW_TMP_DIR', "tmp");

// Identify whether to use Smarty Templating Engine
define('SW_MESSAGE_NODATA', 'No Data was Found Matching Your Criteria');
define('SW_MESSAGE_ERRORLIST', 'Unable To Continue:');
define('SW_MESSAGE_BACK', 'Go Back');
define('SW_MESSAGE_DEBUGLIST', 'Debug:');

// SOAP Environment
define('SW_SOAP_NAMESPACE', 'reportico.org');
define('SW_SOAP_SERVICEBASEURL', 'http://www.reportico.co.uk/swsite/site/tutorials');

// Parameter Defaults
define('SW_DEFAULT_PageSize', 'A4');
define('SW_DEFAULT_PageOrientation', 'Landscape');
define('SW_DEFAULT_TopMargin', "1cm");
define('SW_DEFAULT_BottomMargin', "2cm");
define('SW_DEFAULT_LeftMargin', "1cm");
define('SW_DEFAULT_RightMargin', "1cm");
define('SW_DEFAULT_pdfFont', "Helvetica");
define('SW_DEFAULT_pdfFontSize', "10");

// FPDF parameters
define('FPDF_FONTPATH', './fpdf/font/');


// Graph Defaults
define("SW_GRAPH_ENGINE", "PCHART" );
if ( !defined("SW_GRAPH_ENGINE") || SW_GRAPH_ENGINE == "JPGRAPH" )
{
define('SW_DEFAULT_Font', "Arial");
//advent_light
//Bedizen
//Mukti_Narrow
//calibri
//Forgotte
//GeosansLight
//MankSans
//pf_arma_five
//Silkscreen
//verdana
define('SW_DEFAULT_GraphWidth', 800);
define('SW_DEFAULT_GraphHeight', 400);
define('SW_DEFAULT_GraphWidthPDF', 500);
define('SW_DEFAULT_GraphHeightPDF', 250);
define('SW_DEFAULT_GraphColor', "white");
define('SW_DEFAULT_MarginTop', "40");
define('SW_DEFAULT_MarginBottom', "90");
define('SW_DEFAULT_MarginLeft', "60");
define('SW_DEFAULT_MarginRight', "50");
define('SW_DEFAULT_MarginColor', "white");
define('SW_DEFAULT_XTickLabelInterval', "1");
define('SW_DEFAULT_YTickLabelInterval', "2");
define('SW_DEFAULT_XTickInterval', "1");
define('SW_DEFAULT_YTickInterval', "1");
define('SW_DEFAULT_GridPosition', "back");
define('SW_DEFAULT_XGridDisplay', "none");
define('SW_DEFAULT_XGridColor', "gray");
define('SW_DEFAULT_YGridDisplay', "major");
define('SW_DEFAULT_YGridColor', "gray");
define('SW_DEFAULT_TitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_TitleFontStyle', "Normal");
define('SW_DEFAULT_TitleFontSize', "12");
define('SW_DEFAULT_TitleColor', "black");
define('SW_DEFAULT_XTitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_XTitleFontStyle', "Normal");
define('SW_DEFAULT_XTitleFontSize', "10");
define('SW_DEFAULT_XTitleColor', "black");
define('SW_DEFAULT_YTitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_YTitleFontStyle', "Normal");
define('SW_DEFAULT_YTitleFontSize', "10");
define('SW_DEFAULT_YTitleColor', "black");
define('SW_DEFAULT_XAxisFont', SW_DEFAULT_Font);
define('SW_DEFAULT_XAxisFontStyle', "Normal");
define('SW_DEFAULT_XAxisFontSize', "10");
define('SW_DEFAULT_XAxisFontColor', "black");
define('SW_DEFAULT_XAxisColor', "black");
define('SW_DEFAULT_YAxisFont', SW_DEFAULT_Font);
define('SW_DEFAULT_YAxisFontStyle', "Normal");
define('SW_DEFAULT_YAxisFontSize', "8");
define('SW_DEFAULT_YAxisFontColor', "black");
define('SW_DEFAULT_YAxisColor', "black");
}
else // Use jpgraph
{
define('SW_DEFAULT_Font', "Mukti_Narrow.ttf");
//advent_light.ttf
//Bedizen.ttf
//calibri.ttf
//Forgotte.ttf
//GeosansLight.ttf
//MankSans.ttf
//pf_arma_five.ttf
//Silkscreen.ttf
//verdana.ttf
define('SW_DEFAULT_FontSize', "8");
define('SW_DEFAULT_FontColor', "#303030");
define('SW_DEFAULT_LineColor', "#303030");
define('SW_DEFAULT_BackColor', "#eeeeff");
define('SW_DEFAULT_FontStyle', "Normal");
define('SW_DEFAULT_GraphWidth', 800);
define('SW_DEFAULT_GraphHeight', 400);
define('SW_DEFAULT_GraphWidthPDF', 500);
define('SW_DEFAULT_GraphHeightPDF', 300);
define('SW_DEFAULT_GraphColor', SW_DEFAULT_BackColor);
define('SW_DEFAULT_MarginTop', "50");
define('SW_DEFAULT_MarginBottom', "80");
define('SW_DEFAULT_MarginLeft', "50");
define('SW_DEFAULT_MarginRight', "40");
define('SW_DEFAULT_MarginColor', SW_DEFAULT_BackColor);
define('SW_DEFAULT_XTickLabelInterval', "AUTO");
define('SW_DEFAULT_YTickLabelInterval', "2");
define('SW_DEFAULT_XTickInterval', "1");
define('SW_DEFAULT_YTickInterval', "1");
define('SW_DEFAULT_GridPosition', "back");
define('SW_DEFAULT_XGridDisplay', "major");
define('SW_DEFAULT_XGridColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_YGridDisplay', "major");
define('SW_DEFAULT_YGridColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_TitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_TitleFontStyle', SW_DEFAULT_FontStyle);
define('SW_DEFAULT_TitleFontSize', 12); 
define('SW_DEFAULT_TitleColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_XTitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_XTitleFontStyle', SW_DEFAULT_FontStyle);
define('SW_DEFAULT_XTitleFontSize', SW_DEFAULT_FontSize);
define('SW_DEFAULT_XTitleColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_YTitleFont', SW_DEFAULT_Font);
define('SW_DEFAULT_YTitleFontStyle', SW_DEFAULT_FontStyle);
define('SW_DEFAULT_YTitleFontSize', SW_DEFAULT_FontSize);
define('SW_DEFAULT_YTitleColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_XAxisFont', SW_DEFAULT_Font);
define('SW_DEFAULT_XAxisFontStyle', SW_DEFAULT_FontStyle);
define('SW_DEFAULT_XAxisFontSize', SW_DEFAULT_FontSize);
define('SW_DEFAULT_XAxisFontColor', SW_DEFAULT_FontColor);
define('SW_DEFAULT_XAxisColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_YAxisFont', SW_DEFAULT_Font);
define('SW_DEFAULT_YAxisFontStyle', SW_DEFAULT_FontStyle);
define('SW_DEFAULT_YAxisFontSize', SW_DEFAULT_FontSize);
define('SW_DEFAULT_YAxisFontColor', SW_DEFAULT_LineColor);
define('SW_DEFAULT_YAxisColor', SW_DEFAULT_LineColor);
}

define('SW_JQDEF_timetablemonitor_act_veh_sorttype', 'text');
define('SW_JQDEF_timetablemonitor_start_time_sorttype', 'text');
define('SW_JQDEF_timetablemonitor_lateness_band_sorttype', 'text');
define('SW_JQDEF_timetablemonitor_checkboxes', true);
define('SW_JQDEF_timetablemonitor_primary_key', "schedule_id");
define('SW_JQDEF_timetablemonitor_route_filtertype', 'select');
define('SW_JQDEF_timetablemonitor_route_code_filtertype', 'select');
define('SW_JQDEF_timetablemonitor_act_veh_filtertype', 'select');
//define('SW_JQDEF_timetablemonitor_runningno_editable', true);
define('SW_JQDEF_timetablemonitor_view_trip_width', '30px');
define('SW_JQDEF_timetablemonitor_route_code_width', '30px');
define('SW_JQDEF_timetablemonitor_active_status_width', '30px');
define('SW_JQDEF_timetablemonitor_duty_no_width', '40px');
define('SW_JQDEF_timetablemonitor_trip_no_width', '40px');
define('SW_JQDEF_timetablemonitor_running_no_width', '40px');
define('SW_JQDEF_timetablemonitor_start_time_width', '70px');
define('SW_JQDEF_timetablemonitor_custom_button1', "Cancel");
define('SW_JQDEF_timetablemonitor_custom_button1_ids', "pub_ttb_id");
define('SW_JQDEF_timetablemonitor_custom_button2', "Uncancel");
define('SW_JQDEF_timetablemonitor_custom_button2_ids', "pub_ttb_id");
define('SW_JQDEF_timetablemonitor_custom_button3', "Diversions");
define('SW_JQDEF_timetablemonitor_custom_button3_ids', "pub_ttb_id");

define('SW_JQDEF_specops_operator_code_editable', true);
define('SW_JQDEF_specops_operator_code_filtertype', "readonly");
define('SW_JQDEF_specops_route_code_filtertype', "readonly");
define('SW_JQDEF_specops_route_code_editable', true);
define('SW_JQDEF_specops_map_to_day_type_editable', true);
define('SW_JQDEF_specops_map_to_day_type_filtertype', "select");
define('SW_JQDEF_specops_map_to_day_type_dataurl', "/gateway.php?datasource=rtpihelper&action=eventtypelist");
define('SW_JQDEF_specops_route_id_hidden', true);
define('SW_JQDEF_specops_refkey_hidden', true);
define('SW_JQDEF_specops_operator_id_hidden', true);
define('SW_JQDEF_specops_primary_key', "refkey");

define('SW_JQDEF_destination_primary_key', 'dest_id');



define('SW_JQDEF_locations_location_code_width', '120px');
define('SW_JQDEF_locations_description_width', '250px');
define('SW_JQDEF_locations_routes_width', '250px');
define('SW_JQDEF_locations_latitude_width', '0px');
define('SW_JQDEF_locations_longitude_width', '0px');


// Automatic addition of parameter SW_PROJECT_PASSWORD
define('SW_PROJECT_PASSWORD','');

// Automatic addition of parameter SW_PROJECT_TITLE
define('SW_PROJECT_TITLE','Infohost Real TIme Reporting');

// Automatic addition of parameter SW_DB_ENCODING
define('SW_DB_ENCODING','UTF8');

// Automatic addition of parameter SW_OUTPUT_ENCODING
define('SW_OUTPUT_ENCODING','UTF8');

// Automatic addition of parameter SW_LANGUAGE
define('SW_LANGUAGE','en_gb');

define('SW_JQDEF_laterunners_graph_xlabelcol', "act_veh");
define('SW_JQDEF_laterunners_graph_plotcol1', "lateness_min");
define('SW_JQDEF_laterunners_graph_legend1', "Lateness(Min)");
define('SW_JQDEF_laterunners_graph_plottype1', "bar");
define('SW_JQDEF_laterunners_act_veh_width', '100px');
define('SW_JQDEF_laterunners_xlabel_hidden', true);
define('SW_JQDEF_laterunners_duty_no_hidden', true);
define('SW_JQDEF_laterunners_pub_ttb_id_hidden', true);
define('SW_JQDEF_laterunners_lateness_hidden', true);
define('SW_JQDEF_laterunners_day_hidden', true);
define('SW_JQDEF_laterunners_next_duty_hidden', true);
define('SW_JQDEF_laterunners_next_duty_time_hidden', true);
define('SW_JQDEF_laterunners_diversion_hidden', true);
define('SW_JQDEF_laterunners_journey_to_hidden', true);

define('SW_JQDEF_stoparrperf_location_code_width', '120px');
define('SW_JQDEF_stoparrperf_description_width', '250px');
define('SW_JQDEF_stoparrperf_day_width', '120px');

define('SW_JQDEF_trackingproblems_graph_xlabelcol', "runningno");
define('SW_JQDEF_trackingproblems_graph_plotcol1', "untrackedrbtrips");
define('SW_JQDEF_trackingproblems_graph_legend1', "Untracked Board");
define('SW_JQDEF_trackingproblems_graph_plottype1', "stackedbar");
define('SW_JQDEF_trackingproblems_graph_plotcol2', "tracked_badly");
define('SW_JQDEF_trackingproblems_graph_legend2', "Partially Tracked");
define('SW_JQDEF_trackingproblems_graph_plottype2', "stackedbar");
define('SW_JQDEF_trackingproblems_graph_plotcol3', "drtot");
define('SW_JQDEF_trackingproblems_graph_legend3', "Driver Entry");
define('SW_JQDEF_trackingproblems_graph_plottype3', "stackedbar");
define('SW_JQDEF_trackingproblems_graph_plotcol4', "gptot");
define('SW_JQDEF_trackingproblems_graph_legend4', "Communications");
define('SW_JQDEF_trackingproblems_graph_plottype4', "stackedbar");
define('SW_JQDEF_trackingproblems_graph_plotcol5', "ortot");
define('SW_JQDEF_trackingproblems_graph_legend5', "Off Route" );
define('SW_JQDEF_trackingproblems_graph_plottype5', "stackedbar");
define('SW_JQDEF_trackingproblems_graph_plotcol6', "ottot");
define('SW_JQDEF_trackingproblems_graph_legend6', "Other");
define('SW_JQDEF_trackingproblems_graph_plottype6', "stackedbar");
define('SW_JQDEF_trackingproblems_map_width', '200px');
define('SW_JQDEF_trackingproblems_journey_to_hidden', true);
?>
