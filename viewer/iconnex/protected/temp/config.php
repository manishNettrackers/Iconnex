<?php /*
** Sets envoronment specific veriables such as database connection strings
** and system paths
*/
set_include_path(get_include_path().":../web/ods/iconnex/protected/extensions/reportico:../web/yii/iconnex/protected/config:../web/yii/iconnex/protected/views/webstop");
define('ODS_SITE_NAME', 'Peter Deed Local');
define('ROOT_DIR', '/home/reda/opt/connexionz/miltonk/src/ods/');
define('LOG_DIR', '/home/reda/opt/connexionz/miltonk/src/ods/logs/');
define('TMP_DIR', '/home/reda/opt/connexionz/miltonk/src/ods/tmp/');
define('ICX_RTPI_DB_DRIVER', 'mysql');
define('ICX_RTPI_DB_DRIVER_PDO', 'mysql');
define('ICX_RTPI_DB_DRIVER_REPORTICO', 'pdo_mysql');
define('ICX_RTPI_DB_HOST', '127.0.0.1');
//define('ICX_RTPI_DB_HOST', '192.254.137.196');
define('ICX_RTPI_DB_NAME', 'nttests_ods');
define('ICX_RTPI_DB_USER', 'root');
define('ICX_RTPI_DB_PASSWORD', '');
define('ICX_RTPI_DB_SERVER', 'centdev_tcp');
define('ICX_RTPI_DB_SERVICE', '5130');
define('ICX_RTPI_DB_PROTOCOL', 'onsoctcp');
//define('ICX_RTPI_DB_CONN_STRING', ''.ICX_RTPI_DB_DRIVER.':host='.ICX_RTPI_DB_HOST.'; service='.ICX_RTPI_DB_SERVICE.'; database='.ICX_RTPI_DB_NAME.'; server='.ICX_RTPI_DB_SERVER.'; protocol='.ICX_RTPI_DB_PROTOCOL.';');
//define('ICX_RTPI_DB_CONN_STRING_PDO', ''.ICX_RTPI_DB_DRIVER_PDO.':host='.ICX_RTPI_DB_HOST.'; service='.ICX_RTPI_DB_SERVICE.'; database='.ICX_RTPI_DB_NAME.'; server='.ICX_RTPI_DB_SERVER.'; protocol='.ICX_RTPI_DB_PROTOCOL.';');
define('ICX_RTPI_DB_CONN_STRING', ''.ICX_RTPI_DB_DRIVER.':host='.ICX_RTPI_DB_HOST.'; dbname='.ICX_RTPI_DB_NAME.';');
define('ICX_RTPI_DB_CONN_STRING_PDO', ''.ICX_RTPI_DB_DRIVER_PDO.':host='.ICX_RTPI_DB_HOST.'; dbname='.ICX_RTPI_DB_NAME.';' );
define('ICX_RTPIODS_DB_DRIVER', 'mysql');
define('ICX_RTPIODS_DB_DRIVER_PDO', 'mysql');
define('ICX_RTPIODS_DB_DRIVER_REPORTICO', 'pdo_mysql');
define('ICX_RTPIODS_DB_HOST', '127.0.0.1');
//define('ICX_RTPIODS_DB_HOST', '192.254.137.196');
define('ICX_RTPIODS_DB_NAME', 'nttests_ods');
define('ICX_RTPIODS_DB_USER', 'root');
define('ICX_RTPIODS_DB_PASSWORD', '');
define('ICX_RTPIODS_DB_SERVER', 'centdev_tcp');
define('ICX_RTPIODS_DB_SERVICE', '5130');
define('ICX_RTPIODS_DB_PROTOCOL', 'onsoctcp');
//define('ICX_RTPIODS_DB_CONN_STRING', ''.ICX_RTPIODS_DB_DRIVER.':host='.ICX_RTPIODS_DB_HOST.'; service='.ICX_RTPIODS_DB_SERVICE.'; dbname='.ICX_RTPIODS_DB_NAME.'; server='.ICX_RTPIODS_DB_SERVER.'; protocol='.ICX_RTPIODS_DB_PROTOCOL.';');
//define('ICX_RTPIODS_DB_CONN_STRING_PDO', ''.ICX_RTPIODS_DB_DRIVER_PDO.':host='.ICX_RTPIODS_DB_HOST.'; service='.ICX_RTPIODS_DB_SERVICE.'; dbname='.ICX_RTPIODS_DB_NAME.'; server='.ICX_RTPIODS_DB_SERVER.'; protocol='.ICX_RTPIODS_DB_PROTOCOL.';');

define('ICX_RTPIODS_DB_CONN_STRING', ''.ICX_RTPIODS_DB_DRIVER.':host='.ICX_RTPIODS_DB_HOST.'; dbname='.ICX_RTPIODS_DB_NAME.';');
define('ICX_RTPIODS_DB_CONN_STRING_PDO', ''.ICX_RTPIODS_DB_DRIVER_PDO.':host='.ICX_RTPIODS_DB_HOST.'; dbname='.ICX_RTPIODS_DB_NAME.';');
define('ICX_ODS_DB_DRIVER', 'mysql');
define('ICX_ODS_DB_DRIVER_PDO', 'mysql');
define('ICX_ODS_DB_DRIVER_REPORTICO', 'pdo_mysql');
define('ICX_ODS_DB_HOST', '127.0.0.1');
//define('ICX_ODS_DB_HOST', '192.254.137.196');
define('ICX_ODS_DB_NAME', 'nttests_ods');
define('ICX_ODS_DB_USER', 'root');
define('ICX_ODS_DB_PASSWORD', '');
define('ICX_ODS_DB_SERVER', 'centdev_tcp');
define('ICX_ODS_DB_SERVICE', '5130');
define('ICX_ODS_DB_PROTOCOL', 'onsoctcp');
define('ICX_ODS_DB_CONN_STRING', ''.ICX_ODS_DB_DRIVER.':host='.ICX_ODS_DB_HOST.'; service='.ICX_ODS_DB_SERVICE.'; dbname='.ICX_ODS_DB_NAME.'; server='.ICX_ODS_DB_SERVER.'; protocol='.ICX_ODS_DB_PROTOCOL.';');
define('ICX_ODS_DB_CONN_STRING_PDO', ''.ICX_ODS_DB_DRIVER_PDO.':host='.ICX_ODS_DB_HOST.'; service='.ICX_ODS_DB_SERVICE.'; dbname='.ICX_ODS_DB_NAME.'; server='.ICX_ODS_DB_SERVER.'; protocol='.ICX_ODS_DB_PROTOCOL.';');
define('ICX_ODSAUTH_DB_DRIVER', 'mysql');
define('ICX_ODSAUTH_DB_DRIVER_PDO', 'mysql');
define('ICX_ODSAUTH_DB_DRIVER_REPORTICO', 'pdo_mysql');
define('ICX_ODSAUTH_DB_HOST', '127.0.0.1');
//define('ICX_ODSAUTH_DB_HOST', '192.254.137.196');
define('ICX_ODSAUTH_DB_NAME', 'nttests_ods');
define('ICX_ODSAUTH_DB_USER', 'root');
define('ICX_ODSAUTH_DB_PASSWORD', '');
define('ICX_ODSAUTH_DB_SERVER', 'centdev_tcp');
define('ICX_ODSAUTH_DB_SERVICE', '5130');
define('ICX_ODSAUTH_DB_PROTOCOL', 'onsoctcp');
define('ICX_ODSAUTH_DB_CONN_STRING', ''.ICX_ODSAUTH_DB_DRIVER.':host='.ICX_ODSAUTH_DB_HOST.'; service='.ICX_ODSAUTH_DB_SERVICE.'; dbname='.ICX_ODSAUTH_DB_NAME.'; server='.ICX_ODSAUTH_DB_SERVER.'; protocol='.ICX_ODSAUTH_DB_PROTOCOL.'');
define('ICX_ODSAUTH_DB_CONN_STRING_PDO', ''.ICX_ODSAUTH_DB_DRIVER_PDO.':host='.ICX_ODSAUTH_DB_HOST.'; service='.ICX_ODSAUTH_DB_SERVICE.'; dbname='.ICX_ODSAUTH_DB_NAME.'; server='.ICX_ODSAUTH_DB_SERVER.'; protocol='.ICX_ODSAUTH_DB_PROTOCOL.';');
?>