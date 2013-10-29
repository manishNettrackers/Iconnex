
DROP TABLE IF EXISTS `telem_paesa_fact`;
CREATE TABLE `telem_paesa_fact` (
  `paesa_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `time_since_last` decimal(7,2) default NULL,
  `fuel_economy` decimal(7,3) default NULL,
  `fuel_level` int(11) default NULL,
  `distance_travelled` int(11) default NULL,
  `odometer` int(11) default NULL,
  `max_accel` decimal(7,2) default NULL,
  `max_decel` decimal(7,2) default NULL,
  `max_corner` decimal(7,2) default NULL,
  `avg_rpm` int(11) default NULL,
  `avg_speed` int(11) default NULL,
  `max_speed` int(11) default NULL,
  PRIMARY KEY  (`paesa_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=35523 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `telem_paesa_fact`
--

LOCK TABLES `telem_paesa_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesa_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesa_fact` ENABLE KEYS */;
UNLOCK TABLES;

CREATE INDEX ix_telema_gis_id ON telem_paesa_fact ( gis_id );
CREATE INDEX ix_telema_vehicle_id ON telem_paesa_fact ( vehicle_id );
CREATE INDEX ix_telema_driver_id ON telem_paesa_fact ( driver_id );
CREATE INDEX ix_telema_trip_id ON telem_paesa_fact ( trip_id );
CREATE INDEX ix_telema_date_id ON telem_paesa_fact ( date_id );
CREATE INDEX ix_telema_time_id ON telem_paesa_fact ( time_id );

--
-- Table structure for table `telem_paesb_fact`
--

DROP TABLE IF EXISTS `telem_paesb_fact`;
CREATE TABLE `telem_paesb_fact` (
  `paesb_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `trip_time` decimal(7,2) default NULL,
  `fuel_economy` decimal(7,3) default NULL,
  `fuel_level` int(11) default NULL,
  `distance_travelled` int(11) default NULL,
  `odometer` int(11) default NULL,
  `max_accel` decimal(7,2) default NULL,
  `max_decel` decimal(7,2) default NULL,
  `max_corner` decimal(7,2) default NULL,
  `avg_rpm` int(11) default NULL,
  `avg_speed` int(11) default NULL,
  `max_speed` int(11) default NULL,
  PRIMARY KEY  (`paesb_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=730 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemb_gis_id ON telem_paesb_fact ( gis_id );
CREATE INDEX ix_telemb_vehicle_id ON telem_paesb_fact ( vehicle_id );
CREATE INDEX ix_telemb_driver_id ON telem_paesb_fact ( driver_id );
CREATE INDEX ix_telemb_trip_id ON telem_paesb_fact ( trip_id );
CREATE INDEX ix_telemb_date_id ON telem_paesb_fact ( date_id );
CREATE INDEX ix_telemb_time_id ON telem_paesb_fact ( time_id );

--
-- Dumping data for table `telem_paesb_fact`
--

LOCK TABLES `telem_paesb_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesb_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesb_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesc_fact`
--

DROP TABLE IF EXISTS `telem_paesc_fact`;
CREATE TABLE `telem_paesc_fact` (
  `paesc_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `vin` char(17) default NULL,
  `dtc_count` int(11) default NULL,
  `mil_status` int(11) default NULL,
  `service_interval` int(11) default NULL,
  `vehicle_weight` int(11) default NULL,
  `vehicle_status` char(8) default NULL,
  `fuel_method` int(11) default NULL,
  `odometer_method` int(11) default NULL,
  PRIMARY KEY  (`paesc_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=522 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemb_gis_id ON telem_paesc_fact ( gis_id );
CREATE INDEX ix_telemb_vehicle_id ON telem_paesc_fact ( vehicle_id );
CREATE INDEX ix_telemb_driver_id ON telem_paesc_fact ( driver_id );
CREATE INDEX ix_telemb_trip_id ON telem_paesc_fact ( trip_id );
CREATE INDEX ix_telemb_date_id ON telem_paesc_fact ( date_id );
CREATE INDEX ix_telemb_time_id ON telem_paesc_fact ( time_id );

--
-- Dumping data for table `telem_paesc_fact`
--

LOCK TABLES `telem_paesc_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesc_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesc_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesd_fact`
--

DROP TABLE IF EXISTS `telem_paesd_fact`;
CREATE TABLE `telem_paesd_fact` (
  `paesd_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `dtc_1` char(5) default NULL,
  `dtc_2` char(5) default NULL,
  `dtc_3` char(5) default NULL,
  `dtc_4` char(5) default NULL,
  `dtc_5` char(5) default NULL,
  PRIMARY KEY  (`paesd_fact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemd_gis_id ON telem_paesd_fact ( gis_id );
CREATE INDEX ix_telemd_vehicle_id ON telem_paesd_fact ( vehicle_id );
CREATE INDEX ix_telemd_driver_id ON telem_paesd_fact ( driver_id );
CREATE INDEX ix_telemd_trip_id ON telem_paesd_fact ( trip_id );
CREATE INDEX ix_telemd_date_id ON telem_paesd_fact ( date_id );
CREATE INDEX ix_telemd_time_id ON telem_paesd_fact ( time_id );

--
-- Dumping data for table `telem_paesd_fact`
--

LOCK TABLES `telem_paesd_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesd_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesd_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paese_fact`
--

DROP TABLE IF EXISTS `telem_paese_fact`;
CREATE TABLE `telem_paese_fact` (
  `paese_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `high_res_odo` int(11) default NULL,
  `trip_time` int(11) default NULL,
  `idle_time` int(11) default NULL,
  `harsh_accel` int(11) default NULL,
  `harsh_brake` int(11) default NULL,
  `over_speed` int(11) default NULL,
  `over_rpm` int(11) default NULL,
  `heavy_accel` int(11) default NULL,
  `coasting` int(11) default NULL,
  `cruise_ctrl` int(11) default NULL,
  `power_take_off` int(11) default NULL,
  PRIMARY KEY  (`paese_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=566 DEFAULT CHARSET=latin1;

CREATE INDEX ix_teleme_gis_id ON telem_paese_fact ( gis_id );
CREATE INDEX ix_teleme_vehicle_id ON telem_paese_fact ( vehicle_id );
CREATE INDEX ix_teleme_driver_id ON telem_paese_fact ( driver_id );
CREATE INDEX ix_teleme_trip_id ON telem_paese_fact ( trip_id );
CREATE INDEX ix_teleme_date_id ON telem_paese_fact ( date_id );
CREATE INDEX ix_teleme_time_id ON telem_paese_fact ( time_id );

--
-- Dumping data for table `telem_paese_fact`
--

LOCK TABLES `telem_paese_fact` WRITE;
/*!40000 ALTER TABLE `telem_paese_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paese_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesf_fact`
--

DROP TABLE IF EXISTS `telem_paesf_fact`;
CREATE TABLE `telem_paesf_fact` (
  `paesf_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `total_used` decimal(7,3) default NULL,
  `trip_used` decimal(7,3) default NULL,
  `trip_used_idling` decimal(7,3) default NULL,
  PRIMARY KEY  (`paesf_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31299 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemf_gis_id ON telem_paesf_fact ( gis_id );
CREATE INDEX ix_telemf_vehicle_id ON telem_paesf_fact ( vehicle_id );
CREATE INDEX ix_telemf_driver_id ON telem_paesf_fact ( driver_id );
CREATE INDEX ix_telemf_trip_id ON telem_paesf_fact ( trip_id );
CREATE INDEX ix_telemf_date_id ON telem_paesf_fact ( date_id );
CREATE INDEX ix_telemf_time_id ON telem_paesf_fact ( time_id );

--
-- Dumping data for table `telem_paesf_fact`
--

LOCK TABLES `telem_paesf_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesf_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesf_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesg_fact`
--

DROP TABLE IF EXISTS `telem_paesg_fact`;
CREATE TABLE `telem_paesg_fact` (
  `paesg_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `ignition_source` int(11) default NULL,
  `high_res_odo` int(11) default NULL,
  PRIMARY KEY  (`paesg_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=571 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemg_gis_id ON telem_paesg_fact ( gis_id );
CREATE INDEX ix_telemg_vehicle_id ON telem_paesg_fact ( vehicle_id );
CREATE INDEX ix_telemg_driver_id ON telem_paesg_fact ( driver_id );
CREATE INDEX ix_telemg_trip_id ON telem_paesg_fact ( trip_id );
CREATE INDEX ix_telemg_date_id ON telem_paesg_fact ( date_id );
CREATE INDEX ix_telemg_time_id ON telem_paesg_fact ( time_id );

--
-- Dumping data for table `telem_paesg_fact`
--

LOCK TABLES `telem_paesg_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesg_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesg_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesi_fact`
--

DROP TABLE IF EXISTS `telem_paesi_fact`;
CREATE TABLE `telem_paesi_fact` (
  `paesi_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `avg_model` char(10) default NULL,
  `serial_no` int(11) default NULL,
  `firmware_name` char(20) default NULL,
  `firmware_version` char(10) default NULL,
  `bootloader_version` char(10) default NULL,
  `reset_type` char(1) default NULL,
  `reset_code` int(11) default NULL,
  `boot_code` int(11) default NULL,
  `vehicle_voltage` decimal(7,3) default NULL,
  PRIMARY KEY  (`paesi_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=596 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemi_gis_id ON telem_paesi_fact ( gis_id );
CREATE INDEX ix_telemi_vehicle_id ON telem_paesi_fact ( vehicle_id );
CREATE INDEX ix_telemi_driver_id ON telem_paesi_fact ( driver_id );
CREATE INDEX ix_telemi_trip_id ON telem_paesi_fact ( trip_id );
CREATE INDEX ix_telemi_date_id ON telem_paesi_fact ( date_id );
CREATE INDEX ix_telemi_time_id ON telem_paesi_fact ( time_id );

--
-- Dumping data for table `telem_paesi_fact`
--

LOCK TABLES `telem_paesi_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesi_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesi_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesp_fact`
--

DROP TABLE IF EXISTS `telem_paesp_fact`;
CREATE TABLE `telem_paesp_fact` (
  `paesp_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `band_1` int(11) default NULL,
  `band_2` int(11) default NULL,
  `band_3` int(11) default NULL,
  `band_4` int(11) default NULL,
  `band_5` int(11) default NULL,
  `band_6` int(11) default NULL,
  `band_7` int(11) default NULL,
  `band_8` int(11) default NULL,
  `band_9` int(11) default NULL,
  `band_10` int(11) default NULL,
  `band_11` int(11) default NULL,
  `band_12` int(11) default NULL,
  `band_13` int(11) default NULL,
  `band_14` int(11) default NULL,
  `band_15` int(11) default NULL,
  `band_16` int(11) default NULL,
  `band_17` int(11) default NULL,
  `band_18` int(11) default NULL,
  `band_19` int(11) default NULL,
  `band_20` int(11) default NULL,
  PRIMARY KEY  (`paesp_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=550 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemp_gis_id ON telem_paesp_fact ( gis_id );
CREATE INDEX ix_telemp_vehicle_id ON telem_paesp_fact ( vehicle_id );
CREATE INDEX ix_telemp_driver_id ON telem_paesp_fact ( driver_id );
CREATE INDEX ix_telemp_trip_id ON telem_paesp_fact ( trip_id );
CREATE INDEX ix_telemp_date_id ON telem_paesp_fact ( date_id );
CREATE INDEX ix_telemp_time_id ON telem_paesp_fact ( time_id );

--
-- Dumping data for table `telem_paesp_fact`
--

LOCK TABLES `telem_paesp_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesp_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesp_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesr_fact`
--

DROP TABLE IF EXISTS `telem_paesr_fact`;
CREATE TABLE `telem_paesr_fact` (
  `paesr_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `band_1` int(11) default NULL,
  `band_2` int(11) default NULL,
  `band_3` int(11) default NULL,
  `band_4` int(11) default NULL,
  `band_5` int(11) default NULL,
  `band_6` int(11) default NULL,
  `band_7` int(11) default NULL,
  `band_8` int(11) default NULL,
  `band_9` int(11) default NULL,
  `band_10` int(11) default NULL,
  `band_11` int(11) default NULL,
  `band_12` int(11) default NULL,
  PRIMARY KEY  (`paesr_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=547 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemr_gis_id ON telem_paesr_fact ( gis_id );
CREATE INDEX ix_telemr_vehicle_id ON telem_paesr_fact ( vehicle_id );
CREATE INDEX ix_telemr_driver_id ON telem_paesr_fact ( driver_id );
CREATE INDEX ix_telemr_trip_id ON telem_paesr_fact ( trip_id );
CREATE INDEX ix_telemr_date_id ON telem_paesr_fact ( date_id );
CREATE INDEX ix_telemr_time_id ON telem_paesr_fact ( time_id );

--
-- Dumping data for table `telem_paesr_fact`
--

LOCK TABLES `telem_paesr_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesr_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesr_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paest_fact`
--

DROP TABLE IF EXISTS `telem_paest_fact`;
CREATE TABLE `telem_paest_fact` (
  `paest_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `event_id` int(11) default NULL,
  `duration` int(11) default NULL,
  `threshold` decimal(7,3) default NULL,
  PRIMARY KEY  (`paest_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17060 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemt_gis_id ON telem_paest_fact ( gis_id );
CREATE INDEX ix_telemt_vehicle_id ON telem_paest_fact ( vehicle_id );
CREATE INDEX ix_telemt_driver_id ON telem_paest_fact ( driver_id );
CREATE INDEX ix_telemt_trip_id ON telem_paest_fact ( trip_id );
CREATE INDEX ix_telemt_date_id ON telem_paest_fact ( date_id );
CREATE INDEX ix_telemt_time_id ON telem_paest_fact ( time_id );
--
-- Dumping data for table `telem_paest_fact`
--

LOCK TABLES `telem_paest_fact` WRITE;
/*!40000 ALTER TABLE `telem_paest_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paest_fact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telem_paesv_fact`
--

DROP TABLE IF EXISTS `telem_paesv_fact`;
CREATE TABLE `telem_paesv_fact` (
  `paesv_fact_id` int(11) NOT NULL auto_increment,
  `sourcefile` char(16) default NULL,
  `gis_id` int(11) default NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) default NULL,
  `trip_id` int(11) default NULL,
  `date_id` int(11) default NULL,
  `time_id` int(11) default NULL,
  `vehicle_speed_1` decimal(7,1) default NULL,
  `fuel_rate_1` decimal(7,1) default NULL,
  `vehicle_speed_2` decimal(7,1) default NULL,
  `fuel_rate_2` decimal(7,1) default NULL,
  `vehicle_speed_3` decimal(7,1) default NULL,
  `fuel_rate_3` decimal(7,1) default NULL,
  `no_of_samples_1` int(11) default NULL,
  `no_of_samples_2` int(11) default NULL,
  `no_of_samples_3` int(11) default NULL,
  PRIMARY KEY  (`paesv_fact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30693 DEFAULT CHARSET=latin1;

CREATE INDEX ix_telemv_gis_id ON telem_paesv_fact ( gis_id );
CREATE INDEX ix_telemv_vehicle_id ON telem_paesv_fact ( vehicle_id );
CREATE INDEX ix_telemv_driver_id ON telem_paesv_fact ( driver_id );
CREATE INDEX ix_telemv_trip_id ON telem_paesv_fact ( trip_id );
CREATE INDEX ix_telemv_date_id ON telem_paesv_fact ( date_id );
CREATE INDEX ix_telemv_time_id ON telem_paesv_fact ( time_id );
--
-- Dumping data for table `telem_paesv_fact`
--

LOCK TABLES `telem_paesv_fact` WRITE;
/*!40000 ALTER TABLE `telem_paesv_fact` DISABLE KEYS */;
/*!40000 ALTER TABLE `telem_paesv_fact` ENABLE KEYS */;
UNLOCK TABLES;

