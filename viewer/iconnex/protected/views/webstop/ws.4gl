globals "../lib4gl/sysglb.4gl"

DATABASE centurion
{
--------------------------------------------------------------------------------
	CENTURION@DATA - 4GL Source File
--------------------------------------------------------------------------------
	Module  : WebStop
	Version : $Id: ws.4gl,v 1.1.1.1 2011-10-06 12:21:07 zeriv Exp $
-----------------------------------------------------------------------------

	Produces a report on the arrival times of buses at the specified location

-----------------------------------------------------------------------------
}

GLOBALS
    DEFINE
        g_system_key RECORD LIKE system_key.*
END GLOBALS

DEFINE
	wr_operator		    RECORD LIKE operator.*,
	wr_dcd_param		RECORD LIKE dcd_param.*,
	wr_destination		RECORD LIKE destination.*,
	wr_dcd_route		RECORD LIKE dcd_param.*,
	wr_dcd_op			RECORD LIKE dcd_param.*,
	wr_dcd_countdown	RECORD LIKE dcd_countdown.*,
	wr_old_countdown	RECORD LIKE dcd_countdown.*,
	wr_act_rte			RECORD LIKE active_rt.*,
	wr_vehicle			RECORD LIKE vehicle.*,
	wr_location			RECORD LIKE location.*,
	wr_service			RECORD LIKE service.*,
	wr_act_rte_loc		RECORD LIKE active_rt_loc.*,
	wr_route			RECORD LIKE route.*,
    w_atco_code         varchar(255),
    w_naptan_code       varchar(255)

DEFINE	
	locations			CHAR(50),
	in_locations		CHAR(50),
	in_mode				CHAR(5),
	in_show_veh			CHAR(1),
	in_show_auts		CHAR(1),
    w_display_line      INTEGER,
    m_status            INTEGER,
    m_comp_int          INTERVAL HOUR TO SECOND,
    txt                 CHAR(150)

DEFINE
	m_dcd_countdown	RECORD
		messageType				SMALLINT,
		addressId				INTEGER,
		serviceCode				CHAR(6),
		operatorId				INTEGER,
		unitId					INTEGER,
		journeyId				INTEGER,
		countdownTime			DATETIME YEAR TO SECOND,
		destinationText			CHAR(100),
		wheelchairAccess		INTEGER,
		cntdwn_msg_ver			INTEGER,
        output_pub_hhmm         CHAR(4),
        display_mins            CHAR(4)
	END RECORD

-- -----------------------------------------------------------------------------
-- Function : ws
-- -----------------------------------------------------------------------------
--
-- Parameters
-- ----------
-- in_locations List of location codes, separated by commas, to show buses for.
-- in_mode      Should be admin or stop and determines what is displayed.
-- in_show_veh	Flag of whether or not to show vehicle codes ("1" shows them)
-- in_show_auts	Flag of whether or not to show autoroutes ("1" shows them)
-- -----------------------------------------------------------------------------
FUNCTION ws()
	DEFINE
		msg				    CHAR(200),
		nobus_page		    CHAR(400),
		f_dbsname		    CHAR(200),
        m_auth_dbs          like system_key.key_value,
        m_t_dcdrp_refresh   DATETIME YEAR TO SECOND

	-- Initialise FourJs
	CALL fgl_init4js()

    let f_dbsname = fgl_getenv("CENTDBS")
    if length(f_dbsname) = 0 then
       display "CENTDBS Environment Variable Not Set"
       exit program
    end if
    CONNECT TO f_dbsname
    SQL
        SET ROLE centrole
    END SQL

	SQL
		SET ROLE centrole
	END SQL

    IF NOT get_system_key_syslib("LOCK","AUTHDBS") THEN
       DISPLAY CURRENT YEAR TO SECOND, ": dcd: AUTHDBS System Key Entry Not Found"
       EXIT PROGRAM
    END IF
    LET m_auth_dbs = g_system_key.key_value

	-- Parse parameters
	LET in_locations = ARG_VAL(1)
	IF LENGTH(in_locations) = 0 THEN
		DISPLAY "Parameters:  location,location stop|admin show_veh[0|1] show_auts[0|1]"
		EXIT PROGRAM
	END IF

	LET in_mode = ARG_VAL(2)
	LET in_show_veh = ARG_VAL(3)
	LET in_show_auts = ARG_VAL(4)

	#SET LOCK MODE TO WAIT 5
	SET ISOLATION TO DIRTY READ

	-- Use library to create report to screen
    LET m_t_dcdrp_refresh =  build_dcd_param_webstop(CURRENT - 1 UNITS YEAR, m_auth_dbs, in_locations)
    LET m_status = get_location_details(in_locations)
    LET m_status = webstop_countdowns(in_locations, "WEBSTOP")
	-- CALL display_point_data(in_locations, "A", "Y", "")
    DISPLAY "#<!--C0MPLETE-->"

END FUNCTION

-- dummy function to satisfy the broken lib4gl
FUNCTION redirector(func_name)
	DEFINE func_name like ptmnop.optcmd

    RETURN 1
END FUNCTION

function version_control_ws()

# DO NOT TOUCH ANYTHING IN THIS FUNCTION OR YOU`LL RUIN RCS

	define
      m_cvsrec record
         cvsauthor char(20),
         cvsdate char(20),
         cvsname char(20),
         cvslocker char(20),
         cvslog char(20),
         cvsfile char(20),
         cvsrevision char(20),
         cvssource char(20),
         cvsstate char(20)
      end record

   let m_cvsrec.cvsauthor = "$Author: zeriv $"
   let m_cvsrec.cvsdate = "$Date: 2011-10-06 12:21:07 $"
   let m_cvsrec.cvsname = "$Name:  $"
   let m_cvsrec.cvslocker = "$Locker:  $"
   let m_cvsrec.cvsfile = "$RCSfile: ws.4gl,v $"
   let m_cvsrec.cvsrevision = "$Revision: 1.1.1.1 $"
   let m_cvsrec.cvssource = "$Source: /centurion/dev/Server/web/yii/iconnex/protected/views/webstop/ws.4gl,v $"
   let m_cvsrec.cvsstate = "$State: Exp $"

   display "WS Version ", m_cvsrec.cvsrevision[12,20], " Version Date: ", m_cvsrec.cvsdate[8,18]
   display "----------------------------------------------------------"

end function

-- -----------------------------------------------------------------------------
-- Function : webstop_countdowns
-- -----------------------------------------------------------------------------
--
-- Passes through all arrivals for sign
-- a prepare countdown for them
--
-- Parameters
-- ----------
-- in_location - If supplied will generate countdown for the specified location
-- in_mode     - Will be one of :
--                  WEBSTOP - Generates info suitable for webstop display
--                  SIGNS   - Generates info and sends to stop signs
-- -----------------------------------------------------------------------------
FUNCTION webstop_countdowns(in_location, in_mode)

    DEFINE
        in_location     LIKE location.location_code,
        in_mode         CHAR(8)

	DEFINE
		l_location_id	INTEGER,
		l_update_disp	SMALLINT,
		l_cntdown_time	LIKE active_rt_loc.departure_time,
		l_last_stop		LIKE active_rt_loc.rpat_orderby,
		loccode			LIKE location.location_code,
		c_status		INTEGER,
		old_veh_id		LIKE vehicle.vehicle_id,
		old_loc_id		LIKE vehicle.vehicle_id,
		sql_str			CHAR(100), 
		display_debug	SMALLINT,
		debug			SMALLINT,
        sql_str1		CHAR(300),
		l_display_window INTEGER,
        ch_status        CHAR(20),
		l_t1, l_t2		DATETIME YEAR TO FRACTION,
		l_i1, l_i2		DATETIME YEAR TO FRACTION,
		i_t3			INTERVAL HOUR TO FRACTION,
		lct				INTEGER,
		sel_str         CHAR(500),
        last_sent_sch   INTEGER,
        last_sent_order INTEGER,
        l_status        INTEGER,
        lr_dcd_param    RECORD
    		            operator_id integer,
    		            route_id integer,
                        location_id integer,
                        build_id integer,
                        display_type char(1),
                        day_of_week integer,
                        wef_time datetime hour to second,
                        wet_time datetime hour to second,
                        max_arrivals integer,
                        max_dest_arrivals integer,
                        pred_pub_after integer,
                        disp_pub_after integer,
    		            display_window integer,
    		            countdown_dep_arr char(1),
    		            delivery_mode char(5),
    		            update_thresh_low integer,
    		            update_thresh_high integer,
    		            loop_sleep integer,
    		            disabled char(1)
                        END RECORD

--	SET LOCK MODE TO WAIT 20

	DECLARE c_act_loc CURSOR FOR
	SELECT location.location_id, 
				location.location_code, 
				route.*, active_rt.*, active_rt_loc.*, vehicle.*, 
				route.operator_id,
				t_dcd_param.*, service.*,
                service_patt.dest_id, 
                arrival_status, departure_status
			FROM active_rt, active_rt_loc, publish_tt, service, service_patt, route, operator, route_param, vehicle,
					t_dcd_param, location
			WHERE 1 = 1
			AND active_rt_loc.schedule_id = active_rt.schedule_id
			AND location.location_id = active_rt_loc.location_id
			AND active_rt.vehicle_id = vehicle.vehicle_id
            --AND counted_down = 0
            AND active_rt.route_id = route.route_id
            AND active_rt_loc.actual_est != "C"
			AND route.operator_id = operator.operator_id
			AND route.route_id = t_dcd_param.route_id
			AND active_rt_loc.location_id = t_dcd_param.location_id
			AND route.route_id = route_param.route_id
            AND service.service_id = service_patt.service_id
            AND active_rt_loc.rpat_orderby = service_patt.rpat_orderby
			AND active_rt.pub_ttb_id = publish_tt.pub_ttb_id
			AND publish_tt.service_id = service.service_id
			AND rtpi_status != "N"
			ORDER BY location.location_id, 
					 active_rt_loc.departure_time,
                     active_rt_loc.schedule_id,
                     active_rt_loc.rpat_orderby

	-- Go through each display_point found
	LET l_i1 = CURRENT
	-- DISPLAY " "
	INITIALIZE old_veh_id TO NULL
	INITIALIZE old_loc_id TO NULL
    INITIALIZE last_sent_sch TO NULL
    INITIALIZE last_sent_order TO NULL
    INITIALIZE wr_dcd_countdown.* TO NULL
    INITIALIZE wr_old_countdown.* TO NULL

	-- Consider each active_rt relevant to the current display_point
	-- to decide whether or not to send a message.
	LET lct = 0
	LET l_t1 = CURRENT

	LET display_debug = FALSE
	FOREACH c_act_loc INTO 
			l_location_id, loccode, 
			wr_route.*, wr_act_rte.*, wr_act_rte_loc.*,
			wr_vehicle.*, wr_operator.operator_id, 
			lr_dcd_param.*, wr_service.*,
            wr_destination.dest_id, 
            wr_act_rte_loc.arrival_status, wr_act_rte_loc.departure_status

            LET w_display_line = TRUE

            -- Generate dcd_countodwn record from activer toeu details as may not be one
            -- already for this location
            INITIALIZE wr_dcd_countdown.* TO NULL
            LET wr_dcd_countdown.schedule_id = wr_act_rte.schedule_id
            LET wr_dcd_countdown.rpat_orderby = wr_act_rte_loc.rpat_orderby
            LET wr_dcd_countdown.rtpi_eta_sent = wr_act_rte_loc.arrival_time
            LET wr_dcd_countdown.rtpi_etd_sent = wr_act_rte_loc.departure_time
            LET wr_dcd_countdown.pub_eta_sent = wr_act_rte_loc.arrival_time_pub
            LET wr_dcd_countdown.pub_etd_sent = wr_act_rte_loc.departure_time_pub

            LET lct = lct + 1
			IF old_loc_id IS NULL OR old_loc_id != l_location_id THEN
				-- DISPLAY " "
				-- DISPLAY "*****",CURRENT YEAR TO SECOND, ": webstop_countdowns: LOCATION ", loccode
                CALL sign_countdowns_ws ("CREATE", 0, 0, 0, lr_dcd_param.*, wr_dcd_countdown.*, wr_dcd_countdown.*) RETURNING ch_status
            END IF

			LET txt = CURRENT YEAR TO SECOND, " ", lct using "<<<&", " ", "L:", loccode CLIPPED
			LET txt = txt CLIPPED, " S:", wr_vehicle.vehicle_code USING "<<<<<<<<&", 
					" C:", wr_act_rte.pub_ttb_id USING "<<<<<<<<&",
					" O:", wr_dcd_countdown.rpat_orderby USING "<<<<&",
					" R:", wr_route.route_code CLIPPED, 
					" T:", wr_act_rte.trip_no CLIPPED

			-- ---------------------------------------------------
			-- In order to not allow trip end points to display times
			-- for arrival and departure of two successive trips
			-- Ensure no arrival times are sent to last point
			-- in trip if dcd_param indicates Departure Type
			-- This should probably be extended to not send departures
			-- where dcd_param is set to arrival
			-- ---------------------------------------------------
			SELECT MAX(rpat_orderby)
			    INTO l_last_stop
				FROM active_rt_loc
				WHERE active_rt_loc.schedule_id = wr_act_rte.schedule_id

			LET c_status = STATUS
			IF c_status != 0 OR l_last_stop = wr_dcd_countdown.rpat_orderby
					AND wr_act_rte.start_code != "CONT" THEN
				LET txt = txt CLIPPED, "	Skipping - Last stop of failed to get last stop"
				LET old_veh_id = wr_act_rte.vehicle_id
				LET old_loc_id = l_location_id
			    LET wr_old_countdown.* = wr_dcd_countdown.*
				CONTINUE FOREACH
			END IF

			-- ------------------------------------------------------------
            -- Set cleardown mode to departure for the first stop on a trip
			-- ------------------------------------------------------------
            IF wr_dcd_countdown.rpat_orderby = 1 AND ( lr_dcd_param.countdown_dep_arr IS NULL OR lr_dcd_param.countdown_dep_arr != "D" ) THEN
                LET txt = txt CLIPPED,  "!LOC1->D"
            	LET lr_dcd_param.countdown_dep_arr = "D"
            END IF

			IF wr_act_rte.trip_status = "A" THEN

				if lr_dcd_param.countdown_dep_arr = "A" THEN
					let txt = txt clipped, " ", extend(wr_dcd_countdown.rtpi_eta_sent, hour to second), "/"
				else
					let txt = txt clipped, " ", extend(wr_dcd_countdown.rtpi_etd_sent, hour to second), "/"
				end if

				-- send message and update
				LET m_status = get_countdown(loccode, "COUNTDOWN", lr_dcd_param.*)
				IF m_status = 0 THEN
					LET txt = txt CLIPPED, " IGNORED!!"
                   ELSE
					LET txt = txt CLIPPED, " YES!!"
                    LET last_sent_sch = wr_dcd_countdown.schedule_id
                    LET last_sent_order = wr_dcd_countdown.rpat_orderby
				END IF


			END IF

			IF 1 = 1 OR (display_debug AND w_display_line) THEN
				DISPLAY txt CLIPPED
			END IF
			LET old_veh_id = wr_act_rte.vehicle_id
			LET old_loc_id = l_location_id
			LET wr_old_countdown.* = wr_dcd_countdown.*

	END FOREACH

	LET l_t2 = CURRENT
	LET i_t3 = l_t2 - l_t1
	-- DISPLAY "Time to Generate Stop Times: ", i_t3

	RETURN 0
END FUNCTION

-- -----------------------------------------------------------------------------
-- Function : get_countdown_values
-- -----------------------------------------------------------------------------
--
-- From the dcd_param work out whether countdown should be a
-- departure/arrival value, a scheduled or rtpi time or shown in HHMM 
--
-- Parameters
-- ----------
-- None
-- -----------------------------------------------------------------------------
FUNCTION get_countdown_values(in_dcd_param)
		
	DEFINE
        ch_status               CHAR(20),
		l_sincelast_int			INTERVAL HOUR TO SECOND,
		l_comp_secs				INTEGER,
		l_at_least_every		INTERVAL HOUR TO SECOND,
		curr_time				DATETIME YEAR TO SECOND,
		l_delivery_code         CHAR(1),
        in_dcd_param            RECORD
    		                    operator_id integer,
    		                    route_id integer,
                                location_id integer,
                                build_id integer,
                                display_type char(1),
                                day_of_week integer,
                                wef_time datetime hour to second,
                                wet_time datetime hour to second,
                                max_arrivals integer,
                                max_dest_arrivals integer,
                                pred_pub_after integer,
                                disp_pub_after integer,
    		                    display_window integer,
    		                    countdown_dep_arr char(1),
    		                    delivery_mode char(5),
    		                    update_thresh_low integer,
    		                    update_thresh_high integer,
    		                    loop_sleep integer,
    		                    disabled char(1)
                                END RECORD

    LET wr_old_countdown.* = wr_dcd_countdown.*

    IF wr_vehicle.vehicle_code = "AUT" THEN
        LET wr_vehicle.vehicle_id = 0
        LET wr_dcd_countdown.sch_rtpi_last_sent = "P"
        IF in_dcd_param.countdown_dep_arr = "A" THEN
            IF wr_dcd_countdown.pub_eta_sent IS NOT NULL THEN
                LET wr_dcd_countdown.eta_last_sent = wr_dcd_countdown.pub_eta_sent
            ELSE
                LET wr_dcd_countdown.eta_last_sent = wr_dcd_countdown.rtpi_eta_sent
            END IF
        ELSE
            IF wr_dcd_countdown.pub_etd_sent IS NOT NULL THEN
                LET wr_dcd_countdown.etd_last_sent = wr_dcd_countdown.pub_etd_sent
            ELSE
                LET wr_dcd_countdown.etd_last_sent = wr_dcd_countdown.rtpi_etd_sent
            END IF
        END IF
    ELSE
        LET wr_dcd_countdown.sch_rtpi_last_sent = "R"
        IF in_dcd_param.countdown_dep_arr = "A" THEN
            LET wr_dcd_countdown.eta_last_sent = wr_dcd_countdown.rtpi_eta_sent
        ELSE
            LET wr_dcd_countdown.etd_last_sent = wr_dcd_countdown.rtpi_etd_sent
        END IF

--display "PP VAL ", in_dcd_param.pred_pub_after, " ", in_dcd_param.disp_pub_after
        -- If vehicle more than x minutes away then use published instead or disp published instead
        --LET wr_dcd_countdown.eta_etd_last_sent = wr_dcd_countdown.eta_etd_last_sent + 10 UNITS MINUTE
        LET m_comp_int = wr_dcd_countdown.etd_last_sent - CURRENT
        LET l_comp_secs = HHMMSS_to_Seconds(m_comp_int)
--LET txt = txt clipped, l_comp_secs USING "---&<<<<<<<", "(", wr_dcd_countdown.eta_etd_last_sent, ")", "/", in_dcd_param.pred_pub_after
        IF l_comp_secs > in_dcd_param.pred_pub_after THEN
            IF  in_dcd_param.countdown_dep_arr = "A" AND wr_dcd_countdown.pub_eta_sent IS NOT NULL THEN
                LET txt = txt CLIPPED, " SW->AP", l_comp_secs using "<<<<&", ">", in_dcd_param.pred_pub_after using "<<<<<&"
                LET wr_dcd_countdown.sch_rtpi_last_sent = "P"
                LET wr_dcd_countdown.eta_last_sent = wr_dcd_countdown.pub_eta_sent
            END IF
            IF  in_dcd_param.countdown_dep_arr = "D" AND wr_dcd_countdown.pub_eta_sent IS NOT NULL THEN
                LET txt = txt CLIPPED, " SW->DP", l_comp_secs using "<<<<&", ">", in_dcd_param.pred_pub_after using "<<<<<&"
                LET wr_dcd_countdown.sch_rtpi_last_sent = "P"
                LET wr_dcd_countdown.etd_last_sent = wr_dcd_countdown.pub_etd_sent
            END IF
        END IF

        LET l_comp_secs = HHMMSS_to_Seconds(wr_dcd_countdown.etd_last_sent - CURRENT)
        IF l_comp_secs > in_dcd_param.disp_pub_after AND wr_dcd_countdown.sch_rtpi_last_sent <> "P" THEN
            LET txt = txt CLIPPED, " SW->D"
            LET wr_dcd_countdown.sch_rtpi_last_sent = "P"
        END IF
    END IF

    LET txt = txt CLIPPED,  " ", extend(wr_old_countdown.etd_last_sent, hour to second), "-", wr_dcd_countdown.sch_rtpi_last_sent

END FUNCTION

-- -----------------------------------------------------------------------------
-- Function : should_show
-- -----------------------------------------------------------------------------
--
-- Decides whether or not the times should be sent to a stop
-- Will be true if
--    AUT bus has not been sent for 5 minutes
--    within display_window
--
-- Parameters
-- ----------
-- None
-- -----------------------------------------------------------------------------
FUNCTION should_show(in_dcd_param)
	DEFINE
		l_display_mode	LIKE display_point.display_mode,    
		loccode			char(30)
		
	DEFINE
        ch_status               CHAR(20),
		l_sincelast_int			INTERVAL HOUR TO SECOND,
		l_comp_secs				INTEGER,
		l_at_least_every		INTERVAL HOUR TO SECOND,
		curr_time				DATETIME YEAR TO SECOND,
		l_delivery_code         CHAR(1),
        in_dcd_param            RECORD
    		                    operator_id integer,
    		                    route_id integer,
                                location_id integer,
                                build_id integer,
                                display_type char(1),
                                day_of_week integer,
                                wef_time datetime hour to second,
                                wet_time datetime hour to second,
                                max_arrivals integer,
                                max_dest_arrivals integer,
                                pred_pub_after integer,
                                disp_pub_after integer,
    		                    display_window integer,
    		                    countdown_dep_arr char(1),
    		                    delivery_mode char(5),
    		                    update_thresh_low integer,
    		                    update_thresh_high integer,
    		                    loop_sleep integer,
    		                    disabled char(1)
                                END RECORD


    -- --------------------------------------------------------------------
	-- Is the sign not enabled?
    -- --------------------------------------------------------------------
    IF in_dcd_param.disabled = "X" THEN
        LET txt = txt CLIPPED, "DIS"
		RETURN FALSE
    END IF

    -- --------------------------------------------------------------------
	-- Is the arrival unsuitable for the delivery mode
    -- --------------------------------------------------------------------
	IF LENGTH (in_dcd_param.delivery_mode) > 0 AND in_dcd_param.delivery_mode <> "RCA" THEN
		LET l_delivery_code = wr_act_rte.start_code CLIPPED
		IF NOT contains_string(in_dcd_param.delivery_mode, l_delivery_code) THEN
            LET txt = txt CLIPPED, "DIS"
            RETURN FALSE
		END IF
	END IF

	LET curr_time = CURRENT

    -- --------------------------------------------------------------------
	-- Is arrival time is within display window or has passed
    -- --------------------------------------------------------------------
	LET m_comp_int = wr_dcd_countdown.etd_last_sent - curr_time
	LET l_comp_secs = HHMMSS_to_Seconds(m_comp_int)
	IF l_comp_secs > in_dcd_param.display_window OR l_comp_secs < -60 THEN
        IF l_comp_secs < -60 THEN
            LET txt = txt CLIPPED, " ASSUME COUNTED_DOWN"
            LET w_display_line = TRUE
            RETURN FALSE
        ELSE
            IF l_comp_secs < -60 THEN
                LET w_display_line = TRUE
            END IF
		    LET txt = txt CLIPPED, "WIN", l_comp_secs USING "-<<<<&", "/", in_dcd_param.display_window USING "-<<<<<&"
		    RETURN FALSE
        END IF
	END IF

    -- --------------------------------------------------------------------
	-- Is arrival time is within display window or has passed
    -- --------------------------------------------------------------------
	LET m_comp_int = wr_dcd_countdown.rtpi_etd_sent - curr_time
	LET l_comp_secs = HHMMSS_to_Seconds(m_comp_int)
	IF l_comp_secs > in_dcd_param.display_window OR l_comp_secs < -60 THEN
        IF l_comp_secs < -60 THEN
            LET txt = txt CLIPPED, " ASSUME COUNTED_DOWN"
            LET w_display_line = TRUE
            RETURN FALSE
        ELSE
            IF l_comp_secs < -60 THEN
                LET w_display_line = TRUE
            END IF
		    LET txt = txt CLIPPED, "WIN", l_comp_secs USING "-<<<<&", "/", in_dcd_param.display_window USING "-<<<<<&"
		    RETURN FALSE
        END IF
	END IF

    -- --------------------------------------------------------------------
	-- Has vehicle already arrived/departed, if so clear it down
    -- --------------------------------------------------------------------
    IF ( 
        in_dcd_param.countdown_dep_arr = "A" AND wr_act_rte_loc.arrival_status = "A"  OR
        in_dcd_param.countdown_dep_arr = "D" AND wr_act_rte_loc.departure_status = "A"  
        ) THEN
        LET txt = txt CLIPPED, " Already there Force Clear"
        RETURN false
    END IF

    -- --------------------------------------------------------------------
	--  Does sign already have enough arrivals
    -- --------------------------------------------------------------------
    IF sign_countdowns_ws ("NUMARRS", wr_vehicle.vehicle_id, wr_destination.dest_id, wr_route.route_id, in_dcd_param.*, wr_old_countdown.*, wr_dcd_countdown.*) <> "OK" THEN
        LET txt = txt CLIPPED, "TOO_MANY_ARRS"
        RETURN FALSE
    END IF

    -- --------------------------------------------------------------------
	--  Does sign already have enough arrivals
    -- --------------------------------------------------------------------
    IF sign_countdowns_ws ("NUMARRSPERDEST", wr_vehicle.vehicle_id, wr_destination.dest_id, wr_route.route_id, in_dcd_param.*, wr_old_countdown.*, wr_dcd_countdown.*) <> "OK" THEN
        LET txt = txt CLIPPED, "TOO_MANY_ARRS_FOR_RT_DEST"
        LET w_display_line = TRUE
        RETURN FALSE
    END IF

	-- ---------------------------------------------------
	-- As the bus stop is only able to handle one set of RTPI info 
	-- if this arrival is the second or more arrival of this vehicle at the
    -- sign then convert ito show published time
	-- ---------------------------------------------------
    IF sign_countdowns_ws("DUPVEH", wr_vehicle.vehicle_id, wr_destination.dest_id, wr_route.route_id, in_dcd_param.*, wr_old_countdown.*, wr_dcd_countdown.*) <> "OK" THEN
        LET txt = txt CLIPPED, " DUPV->P"
        LET wr_vehicle.vehicle_code = "AUT"
        LET wr_vehicle.vehicle_id = 0
    END IF

    CALL sign_countdowns_ws("DELIVER", wr_vehicle.vehicle_id, wr_destination.dest_id, wr_route.route_id, in_dcd_param.*, wr_old_countdown.*, wr_dcd_countdown.*)  RETURNING ch_status

	RETURN 1

END FUNCTION

-- -----------------------------------------------------------------------------
-- Function : get_countdown
-- -----------------------------------------------------------------------------
-- Gets information to display on webstpo for a specific countdown
-- -----------------------------------------------------------------------------
FUNCTION get_countdown(loccode, send_mode, in_dcd_param)

	DEFINE	loccode			LIKE location.location_code,
			sel_str			CHAR(500),
			l_counter		INTEGER,
			l_delivery_code CHAR(1),
			l_connect_date	LIKE gprs_mapping.connect_date,
			send_mode		char(10),
			l_counted_down	LIKE dcd_countdown.counted_down,
            do_send         SMALLINT,
            in_dcd_param    RECORD
    		                    operator_id integer,
    		                    route_id integer,
                                location_id integer,
                                build_id integer,
                                display_type char(1),
                                day_of_week integer,
                                wef_time datetime hour to second,
                                wet_time datetime hour to second,
                                max_arrivals integer,
                                max_dest_arrivals integer,
                                pred_pub_after integer,
                                disp_pub_after integer,
    		                    display_window integer,
    		                    countdown_dep_arr char(1),
    		                    delivery_mode char(5),
    		                    update_thresh_low integer,
    		                    update_thresh_high integer,
    		                    loop_sleep integer,
    		                    disabled char(1)
                                END RECORD

	LET l_counter = 0
    LET do_send = TRUE

    -- Analyze the countdown parameters to work out which   
    -- countdown value is applicable whether to send in hhMM format etc
    -- and return previous values
    CALL get_countdown_values(in_dcd_param.*)
    IF should_show (in_dcd_param.*) THEN
        LET m_status = get_countdown_for_display(in_dcd_param.*)
        CALL format_web_stop_data(in_dcd_param.*)

        -- Dont show AUT values that are in the past
        if m_dcd_countdown.countdownTime < CURRENT - 1 UNITS MINUTE  then
           RETURN  1
        end if
        LET l_counter = l_counter + 1

        DISPLAY "ROUTE|", 
            m_dcd_countdown.serviceCode CLIPPED, 
            "|", m_dcd_countdown.destinationText CLIPPED, 
            "|", m_dcd_countdown.destinationText CLIPPED,
            "|", m_dcd_countdown.display_mins CLIPPED,
            "|", m_dcd_countdown.output_pub_hhmm CLIPPED,
            "|", m_dcd_countdown.countdownTime,
            "|", wr_vehicle.vehicle_code clipped, "|"
            

	END IF

	RETURN l_counter

END FUNCTION 

FUNCTION get_countdown_for_display(in_dcd_param)

    DEFINE
        in_dcd_param    RECORD
    		                    operator_id integer,
    		                    route_id integer,
                                location_id integer,
                                build_id integer,
                                display_type char(1),
                                day_of_week integer,
                                wef_time datetime hour to second,
                                wet_time datetime hour to second,
                                max_arrivals integer,
                                max_dest_arrivals integer,
                                pred_pub_after integer,
                                disp_pub_after integer,
    		                    display_window integer,
    		                    countdown_dep_arr char(1),
    		                    delivery_mode char(5),
    		                    update_thresh_low integer,
    		                    update_thresh_high integer,
    		                    loop_sleep integer,
    		                    disabled char(1)
                                END RECORD


	DEFINE
		time_string	CHAR(20),
		l_vehicle_code	LIKE vehicle.vehicle_code,
		l_wheelchair_access	LIKE vehicle.wheelchair_access,
		l_ack_reqd		SMALLINT,
        compare_date	DATETIME YEAR TO SECOND,
		compare_int		INTERVAL HOUR TO SECOND,
		sel_str			CHAR(500),
		sel_str2		CHAR(500),
		l_connect_date	LIKE gprs_mapping.connect_date,
		l_param_value	LIKE unit_param.param_value,
		l_cntdwn_msg	INTEGER,
		l_dest_column	CHAR(30),
		l_count		INTEGER,
        l_log_time      datetime hour to second,
        l_send_ct       INTEGER

    LET m_dcd_countdown.messageType = 0
    LET m_dcd_countdown.serviceCode = wr_service.description

    LET l_vehicle_code = wr_vehicle.vehicle_code

    -- Send unitId of 0 if this is an autoroute
    -- or if this is a CONT and the vehicle is already expected at the stop for a REAL route.
    IF l_vehicle_code = "AUT" OR wr_dcd_countdown.sch_rtpi_last_sent = "P" THEN
        LET m_dcd_countdown.unitId = 0
    END IF
    
    LET m_dcd_countdown.journeyId = wr_act_rte.pub_ttb_id

    IF in_dcd_param.countdown_dep_arr = "A" THEN
        LET time_string = wr_dcd_countdown.eta_last_sent
        LET l_log_time = wr_dcd_countdown.eta_last_sent
        LET m_dcd_countdown.countdownTime = wr_dcd_countdown.eta_last_sent
    ELSE
        LET time_string = wr_dcd_countdown.etd_last_sent
        LET l_log_time = wr_dcd_countdown.etd_last_sent
        LET m_dcd_countdown.countdownTime = wr_dcd_countdown.etd_last_sent
    END IF

    LET l_dest_column = "dest_long"
    LET sel_str2 =
					"SELECT ", l_dest_column CLIPPED,
					" FROM destination, service_patt, publish_tt", 
					" WHERE destination.dest_id = service_patt.dest_id", 
					" AND service_patt.rpat_orderby = ", wr_dcd_countdown.rpat_orderby, 
					" AND service_patt.service_id = publish_tt.service_id",
					" AND publish_tt.pub_ttb_id = ", wr_act_rte.pub_ttb_id
	
    PREPARE dest_sel FROM sel_str2
    EXECUTE dest_sel INTO m_dcd_countdown.destinationText
    
    LET m_dcd_countdown.cntdwn_msg_ver = l_cntdwn_msg
    LET m_dcd_countdown.wheelchairAccess = wr_vehicle.wheelchair_access
    LET m_dcd_countdown.operatorId = wr_operator.operator_id

	RETURN m_status

END FUNCTION

-- ----------------------------------------------------------------------------
-- Function : build_dcd_param_webstop
-- -----------------------------------------------------------------------------
--
-- Creates temporary table containing, for each route the dcd_parameters
-- (display_window, update thresholds etc) to be used taking into
-- account the global dcd_parametes, the operator specific ones and the 
-- route specific ones
--   
-- Parameters
-- ----------
-- None
-- ----------------------------------------------------------------------------
FUNCTION build_dcd_param_webstop(l_t_dp_refresh, l_auth_dbs, l_location_code)
    define
            l_location_code LIKE location.location_code,
	        f_current_time DATETIME YEAR TO SECOND,
			l_t_dp_refresh DATETIME YEAR TO SECOND,
	        f_interval INTERVAL HOUR TO SECOND,
			l_build_id		LIKE unit_build.build_id,
			l_unit_type		LIKE unit_build.unit_type,
			l_build_code	LIKE unit_build.build_code,
			l_param_val		LIKE unit_param.param_value,
			l_auth_dbs		LIKE system_key.key_value,
			l_sel			CHAR(200),
            l_dcd_param     RECORD LIKE dcd_param.*,
            l_now_hhmmss    DATETIME HOUR TO SECOND,
            field_ct        INTEGER,
            l_sql           CHAR(1000),
            field_clause    CHAR(200),
            where_clause    CHAR(200),
            value_clause    CHAR(200)

	LET f_interval = "00:10:00"
	LET f_current_time = CURRENT YEAR TO SECOND


	IF (f_current_time - l_t_dp_refresh) > f_interval THEN

        DECLARE c_dcd_param CURSOR FOR
            SELECT dcd_param.*
             FROM dcd_param
             WHERE dcd_param.build_id IS NULL
             UNION ALL
            SELECT dcd_param.*
              FROM dcd_param, unit_build
              WHERE dcd_param.build_id = unit_build.build_id
                    and build_code = "WEBSTOP"
             ORDER BY level 

		-- DISPLAY "Building DCD Parameters ..."
		WHENEVER ERROR CONTINUE
		DROP TABLE t_dcd_param
		WHENEVER ERROR STOP

		CREATE TEMP TABLE t_dcd_param
  		(
    		operator_id integer,
    		route_id integer,
            location_id integer,
            build_id integer,
            display_type char(1),
            day_of_week integer,
            wef_time datetime hour to second,
            wet_time datetime hour to second,
            max_arrivals integer,
            max_dest_arrivals integer,
            pred_pub_after integer,
            disp_pub_after integer,
    		display_window integer,
    		countdown_dep_arr char(1),
    		delivery_mode char(5),
    		update_thresh_low integer,
    		update_thresh_high integer,
    		loop_sleep integer,
    		disabled char(1)
  		) WITH NO LOG

       -- build table containing every combination of operator/route/location/build

		INSERT INTO t_dcd_param
                (
    		    operator_id,
    		    route_id,
                location_id,
                display_type,
                max_arrivals,
                max_dest_arrivals,
                pred_pub_after,
                disp_pub_after,
    		    display_window,
    		    countdown_dep_arr,
    		    delivery_mode,
    		    update_thresh_low,
    		    update_thresh_high,
    		    loop_sleep )
    		SELECT UNIQUE a.operator_id, a.route_id, 
                e.location_id, 
                    "B",
                    9,
                    9,
                    3600,
                    3600,
    		        0,
    		        "A",
    		        "RCA",
    		        0,
    		        0,
    		        30
            FROM route a, service b, service_patt c, location e
            WHERE a.route_id = b.route_id
            AND b.service_id = c.service_id
            AND c.location_id = e.location_id
            AND TODAY BETWEEN wef_date AND wet_date
            AND c.location_id = e.location_id
            AND (  e.location_code MATCHES l_location_code )

       FOREACH c_dcd_param INTO l_dcd_param.*

                LET field_clause = ""
                LET value_clause = ""
                LET field_ct = 0

                IF l_dcd_param.max_arrivals IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, "," CLIPPED  
                        LET value_clause = value_clause CLIPPED, "," CLIPPED  
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "max_arrivals"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.max_arrivals
                END IF
    
                IF l_dcd_param.max_dest_arrivals IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, "," CLIPPED  
                        LET value_clause = value_clause CLIPPED, "," CLIPPED  
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "max_dest_arrivals"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.max_dest_arrivals
                END IF
    
                IF l_dcd_param.pred_pub_after IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, "," CLIPPED  
                        LET value_clause = value_clause CLIPPED, "," CLIPPED  
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "pred_pub_after"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.pred_pub_after
                END IF
    
                IF l_dcd_param.disp_pub_after IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, "," CLIPPED  
                        LET value_clause = value_clause CLIPPED, "," CLIPPED  
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "disp_pub_after"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.disp_pub_after
                END IF
    
                IF l_dcd_param.display_window IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, "," CLIPPED  
                        LET value_clause = value_clause CLIPPED, "," CLIPPED  
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "display_window"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.display_window
                END IF
    
                IF l_dcd_param.countdown_dep_arr IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, ","
                        LET value_clause = value_clause CLIPPED, ","
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "countdown_dep_arr"
                    LET value_clause = value_clause CLIPPED, "'", l_dcd_param.countdown_dep_arr clipped, "'"
                END IF
    
                IF l_dcd_param.delivery_mode IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, ","
                        LET value_clause = value_clause CLIPPED, ","
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "delivery_mode"
                    LET value_clause = value_clause CLIPPED, "'", l_dcd_param.delivery_mode clipped, "'"
                END IF
    
                IF l_dcd_param.update_thresh_low IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, ","
                        LET value_clause = value_clause CLIPPED, ","
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "update_thresh_low"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.update_thresh_low
                END IF
    
                IF l_dcd_param.update_thresh_high IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, ","
                        LET value_clause = value_clause CLIPPED, ","
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "update_thresh_high"
                    LET value_clause = value_clause CLIPPED, l_dcd_param.update_thresh_high
                END IF
    
                IF l_dcd_param.disabled IS NOT NULL THEN
                    IF field_ct > 0 THEN    
                        LET field_clause = field_clause CLIPPED, ","
                        LET value_clause = value_clause CLIPPED, ","
                    END IF
                    LET field_ct = field_ct + 1
                    LET field_clause = field_clause CLIPPED, "disabled"
                    LET value_clause = value_clause CLIPPED, "'", l_dcd_param.disabled clipped, "'"
                END IF

                -- IF LENGTH ( field_clause ) = 0 THEN
                    -- DISPLAY "NOT SETTING"
                -- END IF

                LET where_clause = " WHERE 1 = 1"

                IF l_dcd_param.operator_id IS NOT NULL THEN
                    LET where_clause = where_clause CLIPPED, 
                        " AND operator_id = ", l_dcd_param.operator_id
                END IF

                IF l_dcd_param.route_id IS NOT NULL THEN
                    LET where_clause = where_clause CLIPPED, 
                        " AND route_id = ", l_dcd_param.route_id
                END IF

                IF l_dcd_param.location_id IS NOT NULL THEN
                    LET where_clause = where_clause CLIPPED, 
                        " AND location_id = ", l_dcd_param.location_id
                END IF

                -- Not relevant for Webstop
                -- IF l_dcd_param.build_id IS NOT NULL THEN
                    -- LET where_clause = where_clause CLIPPED, 
                        -- " AND build_id = ", l_dcd_param.build_id
                -- END IF

                IF l_dcd_param.day_of_week IS NOT NULL THEN
                    IF WEEKDAY(TODAY) != l_dcd_param.day_of_week THEN
                        --DISPLAY "IGNORING DOW SPECIFIER ", l_dcd_param.day_of_week, " vs ", WEEKDAY(TODAY)
                        CONTINUE FOREACH
                    END IF
                END IF

                IF ( l_dcd_param.wef_time IS NOT NULL AND l_dcd_param.wet_time IS NULL ) OR
                    ( l_dcd_param.wef_time IS NOT NULL AND l_dcd_param.wet_time IS NULL ) THEN
                    -- DISPLAY "INVALID DCD EFFECTIVE TIMES", l_dcd_param.wef_time, "/", l_dcd_param.wet_time
                    CONTINUE FOREACH
                END IF

                IF l_dcd_param.wef_time IS NOT NULL AND l_dcd_param.wet_time IS NOT NULL THEN
                    LET l_now_hhmmss = CURRENT HOUR TO SECOND
                    IF l_now_hhmmss < l_dcd_param.wef_time OR l_now_hhmmss > l_dcd_param.wet_time THEN
                        -- DISPLAY "Current ", l_now_hhmmss, " OUTSIDE ", l_dcd_param.wef_time, "/", l_dcd_param.wet_time, " ignoring"
                        CONTINUE FOREACH
                    END IF
                END IF

                IF LENGTH (field_clause) == 0 THEN
                    -- DISPLAY "IGNORED NOTHING TO SET"
                    CONTINUE FOREACH
                END IF
                LET l_sql = "UPDATE t_dcd_param SET ( ", field_clause CLIPPED, ") = ( ",
                        value_clause clipped, ")", where_clause clipped
    
                -- DISPLAY l_sql CLIPPED
		        PREPARE s_sql2 FROM l_sql
                EXECUTE s_sql2
                FREE s_sql2

        END FOREACH

        FREE c_dcd_param

		CREATE INDEX i_t_dcd_param ON t_dcd_param (route_id)
		CREATE INDEX i_t_dcd_param2 ON t_dcd_param (build_id)
		CREATE INDEX i_t_dcd_param3 ON t_dcd_param (location_id)
		
		let l_t_dp_refresh = CURRENT YEAR TO SECOND
	END IF

   RETURN l_t_dp_refresh
	
END FUNCTION 

FUNCTION format_web_stop_data(in_dcd_param)

    DEFINE
        in_dcd_param        RECORD
            operator_id integer,
            route_id integer,
            location_id integer,
            build_id integer,
            display_type char(1),
            day_of_week integer,
            wef_time datetime hour to second,
            wet_time datetime hour to second,
            max_arrivals integer,
            max_dest_arrivals integer,
            pred_pub_after integer,
            disp_pub_after integer,
            display_window integer,
            countdown_dep_arr char(1),
            delivery_mode char(5),
            update_thresh_low integer,
            update_thresh_high integer,
            loop_sleep integer,
            disabled char(1)
        END RECORD

   DEFINE
      in_locations      CHAR(50),
      in_dep_arr_mode      CHAR(1),
      in_show_auts      CHAR(1),
      in_report_file      CHAR(100),
      locations         CHAR(50),
      curr_time         DATETIME YEAR TO SECOND,
      working_time      DATETIME YEAR TO SECOND,
      l_filename         CHAR(30),
      base_dir         CHAR(100),
      out_path         CHAR(100),
      out_file         CHAR(100),
      cmd_str            CHAR(100),
      exit_val         INTEGER,
      route_sel_str      CHAR(1500),
      loc_sel_str         CHAR(1500),
      w_service_id      LIKE service.service_id,
      w_service_desc      LIKE service.description,
      wr_active_rt      RECORD LIKE active_rt.*,
      w_location_id      LIKE location.location_id,
      l_vehicle_code      LIKE vehicle.vehicle_code,
      est_dep_arr         LIKE active_rt_loc.departure_time,
      pub_time         LIKE publish_time.pub_time,
      pub_dep_time      CHAR(4),
      dep_arr_status      LIKE active_rt_loc.departure_status,
      loc_area         LIKE route_area.description,
      loc_description      LIKE location.description,
      destination         LIKE destination.dest_long,
      l_prev_order      LIKE service_patt.rpat_orderby,
      l_prev_etd         LIKE active_rt_loc.departure_time,
      l_passed_prev      SMALLINT,
      passed_prev_int      INTERVAL HOUR TO SECOND,
      l_rpat_order		LIKE service_patt.rpat_orderby,
      wait_time           INTERVAL HOUR TO SECOND,
      wait_time_all_secs  INTEGER,
      wait_time_mins      INTEGER,
      wait_time_secs      INTEGER,
      display_mins        INTEGER,
      l_last_stop		LIKE active_rt_loc.rpat_orderby,
      l_status			  INTEGER,
      est_hr         DATETIME HOUR TO HOUR,
      est_mn         DATETIME MINUTE TO MINUTE

      LET curr_time = CURRENT

      IF in_dcd_param.countdown_dep_arr = "A" THEN
          LET working_time = wr_dcd_countdown.eta_last_sent
      ELSE
          LET working_time = wr_dcd_countdown.etd_last_sent
      END IF

      -- Calculate various values for display times
      -- Actual display time_string is decided in the report
      LET wait_time = working_time - curr_time
		-- include information about buses which have arrived/departed
		-- the stop up to half an hour ago.

      IF wait_time > "-00:30:00" THEN
         -- First get the wait_time in total seconds
         LET wait_time_all_secs = HHMMSS_to_Seconds(wait_time)

         -- Split the total seconds into minutes and seconds
         LET wait_time_mins = wait_time_all_secs / 60
         LET wait_time_secs = wait_time_all_secs - (wait_time_mins * 60)

         -- Round up the seconds to get the minutes to display
         IF wait_time_secs > 30 THEN
            LET display_mins = wait_time_mins + 1
         ELSE
            LET display_mins = wait_time_mins
         END IF

            -- The minimum we display is 1 minute
         IF display_mins = 0 THEN
            LET display_mins = 1
         END IF

        -- Convert the publish departure into simpler format
        LET m_dcd_countdown.output_pub_hhmm = convert_pub_time(pub_time)
      END IF

      LET m_dcd_countdown.output_pub_hhmm = ""
      IF wr_dcd_countdown.sch_rtpi_last_sent = "R" THEN
        LET m_dcd_countdown.display_mins = display_mins USING "<<<<<<&", "m";
        IF wait_time_all_secs < 30 THEN
           LET m_dcd_countdown.display_mins = "Due"
        END IF
        LET est_hr = EXTEND ( wr_dcd_countdown.pub_etd_sent, HOUR TO HOUR )
        LET est_mn = EXTEND ( wr_dcd_countdown.pub_etd_sent, MINUTE TO MINUTE )
        LET m_dcd_countdown.output_pub_hhmm = est_hr, est_mn
      ELSE
         LET est_hr = EXTEND ( working_time, HOUR TO HOUR )
         LET est_mn = EXTEND ( working_time, MINUTE TO MINUTE )
         LET m_dcd_countdown.output_pub_hhmm = est_hr, est_mn
         LET m_dcd_countdown.display_mins = "P";
         IF wait_time_all_secs < -30 THEN
            LET m_dcd_countdown.display_mins = "D";
         END IF
      END IF


      --LET m_dcd_countdown.display_mins = display_mins
   
END FUNCTION

FUNCTION get_location_details(in_loc)

    DEFINE in_loc   LIKE location.location_code

--    SELECT description
 --     INTO wr_location.description
  --    FROM location
   --   WHERE location_code = in_loc
    SELECT location.description, atco_code, naptan_code
        INTO wr_location.description, w_atco_code, w_naptan_code
        FROM location, outer stop
        WHERE stop.atco_code = location.location_code
        AND location.location_code = in_loc;

    DISPLAY "LOCATION||", wr_location.description clipped, "|" , w_atco_code clipped, "|", w_naptan_code clipped, "|", CURRENT HOUR TO SECOND, "||"

    RETURN 0
END FUNCTION
FUNCTION sign_countdowns_ws (in_mode, in_vehicle, in_dest, in_route, in_dcd_param, in_old_countdown, in_new_countdown)

DEFINE
    in_mode             CHAR(20),
    in_vehicle          LIKE vehicle.vehicle_id,
    in_dest             LIKE destination.dest_id,
    in_route            LIKE route.route_id,
    l_count             INTEGER,
    in_new_countdown    RECORD LIKE dcd_countdown.*,
    in_old_countdown    RECORD LIKE dcd_countdown.*,
    changed             SMALLINT,
    in_dcd_param        RECORD
            operator_id integer,
            route_id integer,
            location_id integer,
            build_id integer,
            display_type char(1),
            day_of_week integer,
            wef_time datetime hour to second,
            wet_time datetime hour to second,
            max_arrivals integer,
            max_dest_arrivals integer,
            pred_pub_after integer,
            disp_pub_after integer,
            display_window integer,
            countdown_dep_arr char(1),
            delivery_mode char(5),
            update_thresh_low integer,
            update_thresh_high integer,
            loop_sleep integer,
            disabled char(1)
        END RECORD,
    tmp_interval        INTEGER

    IF in_mode = "CREATE" THEN
        WHENEVER ERROR CONTINUE
        DROP TABLE t_countdowns
        WHENEVER ERROR STOP
        CREATE TEMP TABLE t_countdowns
        (
            arr_no      SERIAL,
            vehicle_id  INTEGER,
            build_id    INTEGER,
            route_id    INTEGER,
            dest_id     INTEGER
        )
        WITH NO LOG
        RETURN "OK"
    END IF

    IF in_mode = "DELIVER" THEN
            INSERT INTO t_countdowns
                VALUES (
                    0, in_vehicle, in_dcd_param.build_id, in_route, in_dest 
                )
            RETURN "OK"
    END IF

    IF in_mode = "NUMARRS" THEN
        SELECT COUNT(*) 
          INTO l_count
          FROM t_countdowns
          --WHERE build_id = in_dcd_param.build_id
        IF l_count >= in_dcd_param.max_arrivals THEN
            RETURN "TOOMANY"
        ELSE 
            RETURN "OK"
        END IF
    END IF

    IF in_mode = "NUMARRSPERDEST" THEN
        SELECT COUNT(*) 
          INTO l_count
          FROM t_countdowns
          WHERE route_id = in_route
            AND dest_id = in_dest
            --AND build_id = in_dcd_param.build_id
        IF l_count >= in_dcd_param.max_dest_arrivals THEN
            RETURN "TOOMANY"
        ELSE 
            RETURN "OK"
        END IF
    END IF

    IF in_mode = "DUPVEH" THEN
        IF in_vehicle = 0 THEN
            RETURN "OK"
        END IF
        SELECT COUNT(*) 
          INTO l_count
          FROM t_countdowns
         WHERE vehicle_id = in_vehicle
           --AND build_id = in_dcd_param.build_id
        IF l_count > 0 THEN
            RETURN "DUPVEH"
        ELSE 
            RETURN "OK"
        END IF
    END IF
 
    IF in_mode = "HASCHANGEDENOUGH" THEN
        LET changed = FALSE
        IF NOT changed THEN
			if in_dcd_param.countdown_dep_arr = "A" THEN
                let tmp_interval = HHMMSS_to_Seconds(in_new_countdown.eta_last_sent - in_old_countdown.eta_last_sent )
            else
                let tmp_interval = HHMMSS_to_Seconds(in_new_countdown.etd_last_sent - in_old_countdown.etd_last_sent )
            end if
        
            IF in_dcd_param.update_thresh_low > tmp_interval OR in_dcd_param.update_thresh_high < tmp_interval THEN
                --DISPLAY " => Tolerance allows countdown update ",
                    --in_dcd_param.update_thresh_low using "-<<<<<&", " < ",
                    --tmp_interval using "-<<<<<&", " < ",
                    --in_dcd_param.update_thresh_high using "-<<<<<&"
                LET changed = TRUE
            ELSE
                INSERT INTO t_countdowns
                    VALUES (
                        0, in_vehicle, in_dcd_param.build_id, in_route, in_dest 
                    )
                --DISPLAY " => Tolerance prevents countdown update ",
                    --in_dcd_param.update_thresh_low using "-<<<<<&", " < ",
                    --tmp_interval using "-<<<<<&", " < ",
                    --in_dcd_param.update_thresh_high using "-<<<<<&"
                LET changed = FALSE
            END IF
        END IF

        IF changed = TRUE THEN
            RETURN "OK"
        ELSE
            RETURN "NOTCHANGED"
        END IF
    END IF

    -- DISPLAY "INVALID SIGN_COUNTDOWN CODE"
    RETURN "INVALID"

END FUNCTION
