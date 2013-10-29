<?php

require_once('iconnex.php');

$iconnex = new iconnex($_pdo);

$dfrom = $_criteria["date"]->get_criteria_value("RANGE1");
$dto = $_criteria["date"]->get_criteria_value("RANGE2");
$rt = $_criteria["route"]->get_criteria_value("VALUE");
$op = $_criteria["operator"]->get_criteria_value("VALUE");

if ( !$iconnex->setDirtyRead() ) return false;
if ( !$iconnex->build_date_range_table($dfrom, $dto) ) return false;

$sql = "CREATE TEMPORARY TABLE t_ops (
    calendar_date date,
    operator_id integer not null ,
    route_id integer,
    service_id integer,
    ttb_event integer,
    ttb_event_code char(10),
    op_event integer not null ,
    map_event integer not null,
    op_event_code char(10),
    map_event_code char(10),
    map_from_date_start date,
    map_from_date_end date,
    map_from_repdt_start date,
    map_from_repdt_end date,
    map_to_date_start date,
    map_to_date_end date,
    map_to_repdt_start date,
    map_to_repdt_end date,
    map_to_repdy_start int,
    map_to_repdy_end int
)";
if ( !$iconnex->executeSQL($sql) ) return false;

if ( $rt )
{
$sql = "
INSERT INTO t_ops (calendar_date, operator_id, route_id, service_id )
SELECT t_days.day, operator.operator_id, route_id, 0
FROM t_days, operator, route_visibility route
WHERE 1 = 1
AND usernm = 'dbmaster'
AND operator.operator_id = route.operator_id
";
if ( $rt )
    $sql .= " AND route.route_id in ( $rt )";
if ( $op )
    $sql .= " AND operator.operator_id in ( $op )";
}
else
{
$sql = "
INSERT INTO t_ops (calendar_date, operator_id, route_id, service_id )
SELECT t_days.day, operator.operator_id, 0, 0
FROM t_days, operator
WHERE 1 = 1
";
if ( $op ) $sql .= " AND operator.operator_id in ( $op )";
}

if ( !$iconnex->executeSQL($sql) ) return false;

$sql="
CREATE INDEX ix_tttb ON t_ops ( calendar_date );
";
if ( !$iconnex->executeSQL($sql) ) return false;

$sql = "
UPDATE t_ops,  special_op, event
SET 
t_ops.op_event = special_op.op_event, 
t_ops.op_event_code = event.event_code, 
t_ops.map_from_date_start = event.spdt_start,
t_ops.map_from_date_end = event.spdt_end
WHERE t_ops.calendar_date = event.spdt_start
AND special_op.op_event = event_id
AND special_op.operator_id = t_ops.operator_id
AND event_tp = 1
AND special_op.route_id = 0
";
if ( $rt )
    $sql .= " AND t_ops.route_id in ( $rt )";
else
    $sql .= " AND special_op.route_id = 0";
if ( !$iconnex->executeSQL($sql) ) return false;

$sql = "
UPDATE t_ops,  special_op, event event_from, event event_to
SET 
t_ops.map_event = special_op.map_event, 
t_ops.map_event_code = event_to.event_code, 
t_ops.map_to_repdy_start = event_to.rpdy_start,
t_ops.map_to_repdy_end = event_to.rpdy_end
WHERE t_ops.calendar_date = event_from.spdt_start
AND special_op.op_event = event_from.event_id
AND special_op.operator_id = t_ops.operator_id
AND event_from.event_tp = 1
AND event_to.event_tp  in (  2, 3 )
AND special_op.map_event = event_to.event_id
AND special_op.route_id = 0
";
if ( !$iconnex->executeSQL($sql) ) return false;



?>
