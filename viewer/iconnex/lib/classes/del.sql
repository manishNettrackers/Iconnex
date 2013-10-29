update snapshot_board_status set ( row_changed, row_status, lateness )
= ( CURRENT, "DELETED", 122 )
where trip_no = "139"
--and row_status = "OK"
