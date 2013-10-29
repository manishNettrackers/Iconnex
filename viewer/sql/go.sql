SELECT menu_no menu_no, menu_name menu_name, app_name app_name, app_url app_url, has_map has_map, has_line has_line, has_grid has_grid, has_report has_report, has_chart has_chart, autorefresh autorefresh, iconnex_application.autorun autorun, refresh_xml refresh_xml, iconnex_menu_user.autorun menu_autorun, show_accordion show_accordion, show_buttons show_buttons, narrtv user_name, run_location run_location 
FROM iconnex_menu, iconnex_menuitem, iconnex_menu_user, iconnex_application, cent_user 
WHERE 1 = 1 
AND iconnex_menu.menu_id = iconnex_menuitem.menu_id 
AND iconnex_menu.menu_id = iconnex_menu_user.menu_id 
AND iconnex_menuitem.app_id = iconnex_application.app_id 
AND iconnex_menu_user.user_id = cent_user.userid 
AND iconnex_menu.menu_id IN ( '1' ) 
AND usernm IN ( 'admin' ) 
ORDER BY menu_name ASC, menu_no ASC
