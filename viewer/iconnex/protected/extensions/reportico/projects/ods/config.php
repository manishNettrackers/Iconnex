<?php
// -----------------------------------------------------------------------------
// -- Reportico -----------------------------------------------------------------
// -----------------------------------------------------------------------------
// Module : config.php
//
// General User Configuration Settings for SeekWell Operation
// -----------------------------------------------------------------------------


putenv('DBDATE=DMY4/');

define('SW_HTTP_BASEDIR','./');
define('SW_HTTP_APP_DIR','/viewer/iconnex/');
define('SW_HTTP_REPORTS_BASEDIR',SW_HTTP_APP_DIR.'protected/extensions/reportico/');
define('SW_HTTP_ASSETS_DIR', SW_HTTP_APP_DIR.'assets/');


// Password required to gain access to the project
define('SW_PROJECT_PASSWORD','');

// Location of SeekWell Top Level Directory From Browser Point of View
define('SW_DEFAULT_PROJECT', 'reports');

// Project Title used at the top of menus
define('SW_PROJECT_TITLE','Open Data Server');

// Identify whether to always run in into Debug Mode
define('SW_ALLOW_OUTPUT', true);
define('SW_ALLOW_DEBUG', true);

// Identify whether Show Criteria is default option
define('SW_DEFAULT_SHOWCRITERIA', false);

// Do we want AJAX controls?
define('AJAX_ENABLED',true);

// Specification of Safe Mode. Turn on SAFE mode by specifying true.
// In SAFE mode, design of reports is allowed but Code and SQL Injection
// are prevented. This means that the designer prevents entry of potentially
// cdangerous ustom PHP source in the Custom Source Section or potentially
// dangerous SQL statements in Pre-Execute Criteria sections
define('SW_SAFE_DESIGN_MODE',false);

// If false prevents any designing of reports
define('SW_ALLOW_MAINTAIN', true);

//  Stylesheets
define('SW_STYLESHEET','stylesheet/cleanandsimple.css');

// DB connection details for ADODB
define('SW_DB_DRIVER','pdo_mysql');
define('SW_DB_USER','root');
define('SW_DB_PASSWORD','root');
define('SW_DB_HOST','127.0.0.1');
define('SW_DB_DATABASE','iconnex');
define('SW_DB_CONNECT_FROM_CONFIG', true);
define('SW_DB_DATEFORMAT','d/m/Y');
define('SW_PREP_DATEFORMAT','d/m/Y');
define('SW_DB_SERVER','centdev_tcp');
define('SW_DB_PROTOCOL','5130');

// Identify temp area
define('SW_TMP_DIR', "tmp");

// Identify whether to use Smarty Templating Engine
define('SW_MESSAGE_NODATA', 'No Data was Found Matching Your Criteria');
define('SW_MESSAGE_ERRORLIST', 'Unable To Continue:');
define('SW_MESSAGE_BACK', 'Go Back');
define('SW_MESSAGE_DEBUGLIST', '<br>Information:');

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

define('SW_JQDEF_maxspeed_speed_mph_editable', true);
define('SW_JQDEF_maxspeed_primary_key', "gis_id");

define('SW_JQDEF_carparks_name_editable', true);
define('SW_JQDEF_carparks_name_sorttype', 'text');
define('SW_JQDEF_carparks_primary_key', "film_id");
define('SW_JQDEF_carparks_spaces_filtertype', 'select');

define('SW_JQDEF_randtime_graph_xlabelcol', "time_id");
define('SW_JQDEF_randtime_graph_plotcol1', "random");
define('SW_JQDEF_randtime_graph_plotcol2', "random2");
define('SW_JQDEF_randtime_graph_plotcol3', "random2");
define('SW_JQDEF_randtime_graph_legend1', "random");
define('SW_JQDEF_randtime_graph_legend2', "random2");
define('SW_JQDEF_randtime_graph_legend3', "random2");
define('SW_JQDEF_randtime_graph_plottype1', "stackedbar");
define('SW_JQDEF_randtime_graph_plottype2', "stackedbar");
define('SW_JQDEF_randtime_graph_plottype3', "line");

define('SW_JQDEF_peoplecounteventrtpi_latitude_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_longitude_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_total_in_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_total_out_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_occupancy_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_hour_no_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_geohash_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_dmy_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_hhmmss_hidden', true);
define('SW_JQDEF_peoplecounteventrtpi_addr_road_width', "150px");
define('SW_JQDEF_peoplecounteventrtpi_time_stamp_width', "150px");


define('SW_JQDEF_snapshotboards_primary_key', "ttkey");
define('SW_JQDEF_snapshotcurrentboards_primary_key', "ttkey");



// Automatic addition of parameter SW_DB_ENCODING
define('SW_DB_ENCODING','UTF8');

// Automatic addition of parameter SW_OUTPUT_ENCODING
define('SW_OUTPUT_ENCODING','UTF8');

// Automatic addition of parameter SW_LANGUAGE
define('SW_LANGUAGE','en_gb');


define('SW_JQDEF_snapshotcurrentboards_graph_xlabelcol', "act_veh");
define('SW_JQDEF_snapshotcurrentboards_graph_plotcol1', "lateness");
define('SW_JQDEF_snapshotcurrentboards_graph_legend1', "Lateness");
define('SW_JQDEF_snapshotcurrentboards_graph_plottype1', "stackedbar");
define('SW_JQDEF_snapshotboards_graph_xlabelcol', "act_veh");
define('SW_JQDEF_snapshotboards_graph_plotcol1', "lateness");
define('SW_JQDEF_snapshotboards_graph_legend1', "Lateness");
define('SW_JQDEF_snapshotboards_graph_plottype1', "stackedbar");
?>

