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
define('SW_HTTP_APP_DIR','/demo/iconnex/');
define('SW_HTTP_REPORTS_BASEDIR',SW_HTTP_APP_DIR.'protected/extensions/reportico/');
define('SW_HTTP_ASSETS_DIR', SW_HTTP_APP_DIR.'assets/');

define('SW_DEFAULT_PROJECT', 'reports');

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
define('SW_SAFE_DESIGN_MODE',false);
define('SW_PROJECT', 'infohost');

// If false prevents any designing of reports
define('SW_ALLOW_MAINTAIN', true);

//  Stylesheets
define('SW_STYLESHEET','stylesheet/cleanandsimple.css');
//define('SW_STYLESHEET','stylesheet/cleanandsimple.css');
//define('SW_STYLESHEET','stylesheet/cleanandsimple.css');
//define('SW_STYLESHEET','stylesheet/cleanandsimple.css');

// DB connection details for ADODB
//define('SW_DB_DRIVER','pdo_informix');
//define('SW_DB_USER','dbmaster');
//define('SW_DB_PASSWORD','read109!!');

//define('SW_DB_HOST','127.0.0.1');
//define('SW_DB_DATABASE','centurion');


define('SW_DB_DRIVER',  ICX_ODSAUTH_DB_DRIVER_REPORTICO);
define('SW_DB_USER', ICX_ODSAUTH_DB_USER);
define('SW_DB_PASSWORD', ICX_ODSAUTH_DB_PASSWORD);
define('SW_DB_HOST', ICX_ODSAUTH_DB_HOST);
define('SW_DB_DATABASE', ICX_ODSAUTH_DB_NAME);
define('SW_DB_SERVER', ICX_ODSAUTH_DB_SERVER);
define('SW_DB_PROTOCOL', ICX_ODSAUTH_DB_PROTOCOL);
define('SW_DB_SERVICE', ICX_ODSAUTH_DB_SERVICE);

define('SW_DB_CONNECT_FROM_CONFIG', true);
define('SW_DB_DATEFORMAT','d/m/Y');
define('SW_PREP_DATEFORMAT','d/m/Y');

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
/*
define('SW_DEFAULT_GraphWidth', 800);
define('SW_DEFAULT_GraphHeight', 400);
define('SW_DEFAULT_GraphWidthPDF', 500);
define('SW_DEFAULT_GraphHeightPDF', 250);
define('SW_DEFAULT_GraphColor', "yellow");
define('SW_DEFAULT_MarginTop', "20");
define('SW_DEFAULT_MarginBottom', "80");
define('SW_DEFAULT_MarginLeft', "50");
define('SW_DEFAULT_MarginRight', "50");
define('SW_DEFAULT_MarginColor', "red");
define('SW_DEFAULT_XTickLabelInterval', "4");
define('SW_DEFAULT_YTickLabelInterval', "2");
define('SW_DEFAULT_XTickInterval', "1");
define('SW_DEFAULT_YTickInterval', "1");
define('SW_DEFAULT_GridPosition', "back");
define('SW_DEFAULT_XGridDisplay', "none");
define('SW_DEFAULT_XGridColor', "gray");
define('SW_DEFAULT_YGridDisplay', "major");
define('SW_DEFAULT_YGridColor', "gray");
define('SW_DEFAULT_TitleFont', "Font2");
define('SW_DEFAULT_TitleFontStyle', "Normal");
define('SW_DEFAULT_TitleFontSize', "12");
define('SW_DEFAULT_TitleColor', "black");
define('SW_DEFAULT_XTitleFont', "Font1");
define('SW_DEFAULT_XTitleFontStyle', "Normal");
define('SW_DEFAULT_XTitleFontSize', "12");
define('SW_DEFAULT_XTitleColor', "black");
define('SW_DEFAULT_YTitleFont', "Font1");
define('SW_DEFAULT_YTitleFontStyle', "Normal");
define('SW_DEFAULT_YTitleFontSize', "12");
define('SW_DEFAULT_YTitleColor', "black");
define('SW_DEFAULT_XAxisFont', "Font1");
define('SW_DEFAULT_XAxisFontStyle', "Normal");
define('SW_DEFAULT_XAxisFontSize', "12");
define('SW_DEFAULT_XAxisFontColor', "black");
define('SW_DEFAULT_XAxisColor', "black");
define('SW_DEFAULT_YAxisFont', "Font1");
define('SW_DEFAULT_YAxisFontStyle', "Normal");
define('SW_DEFAULT_YAxisFontSize', "12");
define('SW_DEFAULT_YAxisFontColor', "black");
define('SW_DEFAULT_YAxisColor', "black");
*/

define('SW_JQDEF_tripcancelbystop_route_code_editable', true);
define('SW_JQDEF_tripcancelbystop_cancel_editable', true);
define('SW_JQDEF_tripcancelbystop_cancel_edittype', 'checkbox');
define('SW_JQDEF_tripcancelbystop_primary_key', "ttb_id");

define('SW_JQDEF_tripcancel_route_code_editable', true);
define('SW_JQDEF_tripcancel_cancel_editable', true);
define('SW_JQDEF_tripcancel_cancel_edittype', 'checkbox');
define('SW_JQDEF_tripcancel_primary_key', "ttb_id");
//define('SW_JQDEF_tripcancel_cancel_editoptions', "1:0" );
define('SW_JQDEF_timetab1_trip_no_width', '30');
define('SW_JQDEF_timetab1_pub_time_width', '120');
define('SW_JQDEF_timetab1_route_code_width', '60');

define('SW_JQDEF_displaypoint_primary_key', "key");
define('SW_JQDEF_displaypoint_description_width', '180');
define('SW_JQDEF_displaypoint_location_code_width', '120');
define('SW_JQDEF_displaypoint_build_code_editable', true);
define('SW_JQDEF_displaypoint_message_time_width', 120);
define('SW_JQDEF_displaypoint_build_code_width', 100);
define('SW_JQDEF_displaypoint_routes_hidden', true);
define('SW_JQDEF_displaypoint_latitude_hidden', true);
define('SW_JQDEF_displaypoint_longitude_hidden', true);
define('SW_JQDEF_displaypoint_bay_no_hidden', true);
define('SW_JQDEF_displaypoint_area_hidden', true);
define('SW_JQDEF_displaypoint_key_hidden', true);
define('SW_JQDEF_displaypoint_build_id_hidden', true);
define('SW_JQDEF_displaypoint_last_impact_hidden', true);
define('SW_JQDEF_displaypoint_impact_count_hidden', true);
define('SW_JQDEF_displaypoint_bootup_count_hidden', true);
define('SW_JQDEF_displaypoint_last_bootup_hidden', true);
define('SW_JQDEF_displaypoint_last_active_hour_hidden', true);
define('SW_JQDEF_displaypoint_last_active_day_hidden', true);

// Graph Defaults
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
define('SW_DEFAULT_BackColor', "#ccddff");
define('SW_DEFAULT_FontStyle', "Normal");
define('SW_DEFAULT_GraphWidth', 1000);
define('SW_DEFAULT_GraphHeight', 600);
define('SW_DEFAULT_GraphWidthPDF', 500);
define('SW_DEFAULT_GraphHeightPDF', 250);
define('SW_DEFAULT_GraphColor', SW_DEFAULT_BackColor);
define('SW_DEFAULT_MarginTop', "50");
define('SW_DEFAULT_MarginBottom', "100");
define('SW_DEFAULT_MarginLeft', "120");
define('SW_DEFAULT_MarginRight', "40");
define('SW_DEFAULT_MarginColor', SW_DEFAULT_BackColor);
define('SW_DEFAULT_XTickLabelInterval', "4");
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

// Automatic addition of parameter SW_PROJECT_PASSWORD
define('SW_PROJECT_PASSWORD', '');

// Automatic addition of parameter SW_PROJECT_TITLE
define('SW_PROJECT_TITLE', 'Informix RTI on ODS');

// Automatic addition of parameter SW_DB_ENCODING
define('SW_DB_ENCODING', 'UTF8');

// Automatic addition of parameter SW_OUTPUT_ENCODING
define('SW_OUTPUT_ENCODING', 'UTF8');

// Automatic addition of parameter SW_LANGUAGE
define('SW_LANGUAGE', 'en_gb');
?>
