<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>

<script>
    $.ajax({
        
        datatype : "json",
        type : "GET",
        url : "index.php?r=buses/index",
        data : {
            type : "timetablemonitor",
            outputformat : "jqgrid"
        },
        success : function (responsedata){
             
            console.log(responsedata)
        }   
        
    });
    
</script>

<?
 

?>