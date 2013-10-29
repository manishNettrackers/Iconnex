/* 
 * javascript file with function to handle the timetableviewer criteria form
 * author reda benjli
 */


/* the url to query in order to get the jgrid json  */
var url     = 'index.php?r=buses/timetableviewer';

/* initiate the param variable */
var params = '';
 
 /* each time fields are changed update the values */
$(".field").live('change',function(){
 
    /* the params to be pased to the url */
      params  = {
        type : "timetablemonitor",
        outputformat : "jqgrid",
        update       : 1,
        datefrom     : $("#User_daterangefrom").val(), //'2013-03-24',
        dateto       : $("#User_daterangeto").val(), //2013-03-26'  
        timefrom     : $("#User_timerangefrom").val(), //'00:00:00',
        timeto       : $("#User_timerangeto").val(), //23:59:59'  
        operatorid   : $("#operator_operator_code option:selected").attr("value"),
        routeid      : $("#Route_route_code option:selected").attr("value"),
        dutynumber   : $("#User_dutynumber").val(),
        runingboard  : $("#User_runingboard").val()
    };
});
/* */
$('#timetableviewercriteriabutton').click(function() {
    getGridOutput('type', parentelementobject.sessionid, url, params, false, false)
    console.log(params)
}); 
/* */
$("#User_daterangefrom").datepicker({ 
    defaultDate: "+1w",
    dateFormat : 'yy-mm-dd',
    changeMonth: true,
    numberOfMonths: 1,
    onClose: function( selectedDate ) {
        $( "#User_daterangeto" ).datepicker( "option", "minDate", selectedDate );
    }
            
}
    
);
/* */ 
$("#User_daterangeto").datepicker({   
    defaultDate: "+1w",
    changeMonth: true,
    dateFormat : 'yy-mm-dd',
    numberOfMonths: 1,
    onClose: function( selectedDate ) {
        $( "#User_daterangeto" ).datepicker( "option", "minDate", selectedDate );
    }           
}  
);
/* */
$("#User_timerangefrom").clockpick({
    starthour: 0,
    endhour :23,
    layout : 'vertical',
    showminutes : false,
    military : true
}); 
/* */
$("#User_timerangeto").clockpick({
    starthour: 0,
    endhour :23,
    layout : 'vertical',
    showminutes : false,
    military : true
}); 
   
 