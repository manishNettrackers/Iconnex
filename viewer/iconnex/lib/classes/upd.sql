dbaccess centurion <<!
update snapshot_board_status set ( row_changed, row_status, duty_no )
= ( CURRENT, "OK", "$2" )
where trip_no = "$1"
--and row_status = "OK"
!
